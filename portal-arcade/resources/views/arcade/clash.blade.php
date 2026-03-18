@extends('layouts.arcade')
@section('title', 'Mini Clash - Arcade')

@section('content')
<div class="bg-gray-200 dark:bg-gray-800 p-6 rounded-xl shadow-2xl relative w-full max-w-md text-center border-4 border-blue-600">
    <a href="{{ route('arcade.index') }}" class="absolute -top-4 -right-4 bg-red-600 text-white w-10 h-10 rounded-full font-bold text-xl border-2 border-white hover:bg-red-700 flex items-center justify-center decoration-transparent z-50">&times;</a>
    
    <div class="flex justify-between items-center mb-2 px-4">
        <h2 class="text-2xl font-black text-red-500 uppercase tracking-widest drop-shadow-md">Torre Inimiga: <span id="enemy-hp">1000</span></h2>
    </div>
    
    <canvas id="clashCanvas" width="360" height="500" class="bg-green-600 border-4 border-gray-900 mx-auto cursor-pointer rounded-lg shadow-inner"></canvas>
    
    <div class="flex justify-between items-center mt-2 px-4">
        <h2 class="text-2xl font-black text-blue-500 uppercase tracking-widest drop-shadow-md">Sua Torre: <span id="player-hp">1000</span></h2>
    </div>
    
    <div class="mt-4 bg-gray-900 p-2 rounded-lg border-2 border-fuchsia-500 flex justify-between items-center">
        <span class="text-fuchsia-400 font-bold text-xl">💧 ELIXIR:</span>
        <div class="w-full h-6 bg-gray-700 mx-4 rounded-full overflow-hidden border border-gray-500">
            <div id="elixir-bar" class="h-full bg-fuchsia-500 w-0 transition-all duration-200"></div>
        </div>
        <span id="elixir-text" class="text-fuchsia-400 font-black text-xl">0/10</span>
    </div>
    <p class="mt-2 text-sm text-gray-500 font-bold">Clique na parte de baixo do campo para gastar 3💧 e invocar um soldado!</p>
</div>
@endsection

@push('scripts')
<script>
window.onload = function() {
    const canvas = document.getElementById('clashCanvas');
    const ctx = canvas.getContext('2d');

    let elixir = 5;
    let playerTowerHP = 1000;
    let enemyTowerHP = 1000;
    let gameActive = true;
    let troops = []; // Guarda todas as tropas (azuis e vermelhas)

    // Aumentar elixir a cada 1 segundo
    setInterval(() => {
        if(gameActive && elixir < 10) {
            elixir++;
            document.getElementById('elixir-text').innerText = elixir + "/10";
            document.getElementById('elixir-bar').style.width = (elixir * 10) + "%";
        }
    }, 1000);

    // IA do Inimigo: Joga cartas aleatoriamente
    setInterval(() => {
        if(!gameActive) return;
        // 40% de chance de spawnar um inimigo a cada 2 segundos
        if(Math.random() < 0.4) {
            let spawnX = Math.random() * (canvas.width - 40) + 20;
            troops.push({ x: spawnX, y: 80, hp: 100, isPlayer: false, color: '#ef4444', radius: 10, speed: 1 });
        }
    }, 2000);

    // Jogador clica para invocar
    canvas.addEventListener('mousedown', (e) => {
        if(!gameActive) return;
        const rect = canvas.getBoundingClientRect();
        const mouseX = e.clientX - rect.left;
        const mouseY = e.clientY - rect.top;

        // Só pode invocar do meio do campo para baixo e se tiver 3 de elixir
        if(mouseY > canvas.height / 2 && elixir >= 3) {
            elixir -= 3;
            document.getElementById('elixir-text').innerText = Math.floor(elixir) + "/10";
            document.getElementById('elixir-bar').style.width = (elixir * 10) + "%";
            
            troops.push({ x: mouseX, y: mouseY, hp: 100, isPlayer: true, color: '#3b82f6', radius: 10, speed: -1.5 });
        }
    });

    function drawField() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Rio no meio
        ctx.fillStyle = '#0ea5e9'; // Azul água
        ctx.fillRect(0, canvas.height/2 - 20, canvas.width, 40);
        
        // Pontes
        ctx.fillStyle = '#78350f'; // Marrom
        ctx.fillRect(60, canvas.height/2 - 20, 60, 40);
        ctx.fillRect(canvas.width - 120, canvas.height/2 - 20, 60, 40);

        // Torre Inimiga (Topo)
        ctx.fillStyle = '#b91c1c';
        ctx.fillRect(canvas.width/2 - 40, 10, 80, 50);
        
        // Torre Jogador (Base)
        ctx.fillStyle = '#1d4ed8';
        ctx.fillRect(canvas.width/2 - 40, canvas.height - 60, 80, 50);
    }

    function gameLoop() {
        if(!gameActive) return;
        requestAnimationFrame(gameLoop);
        drawField();

        // Lógica das Tropas
        for(let i = 0; i < troops.length; i++) {
            let t1 = troops[i];
            let isFighting = false;

            // Checar combate com outras tropas
            for(let j = 0; j < troops.length; j++) {
                if(i === j) continue;
                let t2 = troops[j];
                
                // Se forem de times diferentes e estiverem perto (colisão)
                if(t1.isPlayer !== t2.isPlayer) {
                    let dist = Math.hypot(t1.x - t2.x, t1.y - t2.y);
                    if(dist < t1.radius + t2.radius + 5) {
                        isFighting = true;
                        t1.hp -= 1; // Toma dano contínuo enquanto encosta
                    }
                }
            }

            // Mover se não estiver lutando
            if(!isFighting) {
                t1.y += t1.speed;
            }

            // Checar dano nas torres
            if(t1.isPlayer && t1.y <= 60) {
                enemyTowerHP -= 2;
                t1.hp = 0; // Morre ao bater na torre
            }
            if(!t1.isPlayer && t1.y >= canvas.height - 60) {
                playerTowerHP -= 2;
                t1.hp = 0;
            }

            // Desenhar tropa
            ctx.beginPath();
            ctx.arc(t1.x, t1.y, t1.radius, 0, Math.PI * 2);
            ctx.fillStyle = t1.color;
            ctx.fill();
            ctx.lineWidth = 2;
            ctx.strokeStyle = '#000';
            ctx.stroke();
            
            // Barra de vida da tropa
            ctx.fillStyle = 'red';
            ctx.fillRect(t1.x - 10, t1.y - 15, 20, 4);
            ctx.fillStyle = '#22c55e';
            ctx.fillRect(t1.x - 10, t1.y - 15, (t1.hp/100)*20, 4);
        }

        // Limpar tropas mortas
        troops = troops.filter(t => t.hp > 0);

        // Atualizar HP na tela
        document.getElementById('enemy-hp').innerText = Math.max(0, enemyTowerHP);
        document.getElementById('player-hp').innerText = Math.max(0, playerTowerHP);

        // Condições de Vitória/Derrota
        if(enemyTowerHP <= 0) {
            gameActive = false;
            setTimeout(() => alert("👑 VITÓRIA! Você destruiu a torre inimiga!"), 100);
        } else if (playerTowerHP <= 0) {
            gameActive = false;
            setTimeout(() => alert("💀 DERROTA! Sua torre caiu!"), 100);
        }
    }

    // Atualiza a barrinha de elixir inicial
    document.getElementById('elixir-text').innerText = elixir + "/10";
    document.getElementById('elixir-bar').style.width = (elixir * 10) + "%";
    
    gameLoop(); // Inicia o jogo
};
</script>
@endpush