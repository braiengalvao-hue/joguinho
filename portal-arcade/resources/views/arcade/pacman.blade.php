@extends('layouts.arcade')
@section('title', 'Pacman - Arcade')

@section('content')
<div class="bg-black p-6 rounded-xl shadow-2xl relative w-full max-w-2xl text-center border-4 border-blue-800">
    <a href="{{ route('arcade.index') }}" class="absolute -top-4 -right-4 bg-red-600 text-white w-10 h-10 rounded-full font-bold text-xl border-2 border-white hover:bg-red-700 flex items-center justify-center decoration-transparent">&times;</a>
    <h2 class="text-3xl font-black mb-4 text-yellow-400 uppercase tracking-widest">Pac-Man</h2>
    
    <canvas id="pacmanCanvas" width="400" height="400" class="bg-black border-2 border-blue-900 mx-auto"></canvas>
    
    <div class="flex justify-between items-center mt-4 px-8">
        <p id="pacScore" class="text-white font-mono text-xl">Pontuação: 0</p>
        <button onclick="location.reload()" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-1 px-4 rounded">Reiniciar</button>
    </div>
</div>
@endsection

@push('scripts')
<script>
window.onload = function() {
    const canvas = document.getElementById('pacmanCanvas');
    const ctx = canvas.getContext('2d');
    const tileSize = 20;
    let score = 0;
    let gameActive = true;
    
    // 1 = Parede, 2 = Bolinha, 0 = Vazio
    const map = [
        [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],
        [1,2,2,2,2,2,2,2,2,1,1,2,2,2,2,2,2,2,2,1],
        [1,2,1,1,2,1,1,1,2,1,1,2,1,1,1,2,1,1,2,1],
        [1,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,1],
        [1,2,1,1,2,1,2,1,1,1,1,1,1,2,1,2,1,1,2,1],
        [1,2,2,2,2,1,2,2,2,1,1,2,2,2,1,2,2,2,2,1],
        [1,1,1,1,2,1,1,1,0,1,1,0,1,1,1,2,1,1,1,1],
        [0,0,0,1,2,1,0,0,0,0,0,0,0,0,1,2,1,0,0,0],
        [1,1,1,1,2,1,0,1,1,0,0,1,1,0,1,2,1,1,1,1],
        [0,0,0,0,2,0,0,1,0,0,0,0,1,0,0,2,0,0,0,0],
        [1,1,1,1,2,1,0,1,1,1,1,1,1,0,1,2,1,1,1,1],
        [0,0,0,1,2,1,0,0,0,0,0,0,0,0,1,2,1,0,0,0],
        [1,1,1,1,2,1,2,1,1,1,1,1,1,2,1,2,1,1,1,1],
        [1,2,2,2,2,2,2,2,2,1,1,2,2,2,2,2,2,2,2,1],
        [1,2,1,1,2,1,1,1,2,1,1,2,1,1,1,2,1,1,2,1],
        [1,2,2,1,2,2,2,2,2,0,0,2,2,2,2,2,1,2,2,1],
        [1,1,2,1,2,1,2,1,1,1,1,1,1,2,1,2,1,2,1,1],
        [1,2,2,2,2,1,2,2,2,1,1,2,2,2,1,2,2,2,2,1],
        [1,2,1,1,1,1,1,1,2,1,1,2,1,1,1,1,1,1,2,1],
        [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1]
    ];

    let pac = { x: 9, y: 15, vx: 0, vy: 0, angle: 0 };
    
    // Os 4 fantasmas clássicos
    let ghosts = [
        { x: 9, y: 7, vx: 1, vy: 0, color: 'red' },      // Blinky
        { x: 10, y: 7, vx: -1, vy: 0, color: '#ffb8ff' },// Pinky
        { x: 9, y: 9, vx: 0, vy: -1, color: '#00ffff' }, // Inky
        { x: 10, y: 9, vx: 0, vy: 1, color: '#ffb852' }  // Clyde
    ];

    document.addEventListener("keydown", e => {
        if(!gameActive) return;
        // Salva a direção desejada e rotaciona a boca
        if(e.key === "ArrowLeft") { pac.vx = -1; pac.vy = 0; pac.angle = Math.PI; }
        if(e.key === "ArrowUp") { pac.vx = 0; pac.vy = -1; pac.angle = -Math.PI/2; }
        if(e.key === "ArrowRight") { pac.vx = 1; pac.vy = 0; pac.angle = 0; }
        if(e.key === "ArrowDown") { pac.vx = 0; pac.vy = 1; pac.angle = Math.PI/2; }
    });

    function drawMap() {
        ctx.fillStyle = "black"; ctx.fillRect(0, 0, canvas.width, canvas.height);
        for(let r = 0; r < map.length; r++){
            for(let c = 0; c < map[r].length; c++){
                if(map[r][c] === 1) { 
                    ctx.fillStyle = "#1d4ed8"; // Azul do labirinto
                    ctx.fillRect(c*tileSize, r*tileSize, tileSize, tileSize); 
                }
                else if(map[r][c] === 2) { 
                    ctx.fillStyle = "#facc15"; // Bolinhas amarelas
                    ctx.beginPath(); ctx.arc(c*tileSize + 10, r*tileSize + 10, 3, 0, Math.PI*2); ctx.fill();
                }
            }
        }
    }

    function drawGhosts() {
        ghosts.forEach(g => {
            let px = g.x * tileSize + 10;
            let py = g.y * tileSize + 10;
            
            // Corpo do fantasma
            ctx.fillStyle = g.color;
            ctx.beginPath();
            ctx.arc(px, py - 2, 8, Math.PI, 0); // Cabeça arredondada
            ctx.lineTo(px + 8, py + 8); // Base direita
            ctx.lineTo(px - 8, py + 8); // Base esquerda
            ctx.fill();
            
            // Olhos (Brancos)
            ctx.fillStyle = "white";
            ctx.beginPath(); ctx.arc(px - 3, py - 3, 3, 0, Math.PI*2); ctx.fill();
            ctx.beginPath(); ctx.arc(px + 3, py - 3, 3, 0, Math.PI*2); ctx.fill();
            
            // Pupilas (Azuis, olhando para onde andam)
            ctx.fillStyle = "blue";
            ctx.beginPath(); ctx.arc(px - 3 + g.vx, py - 3 + g.vy, 1.5, 0, Math.PI*2); ctx.fill();
            ctx.beginPath(); ctx.arc(px + 3 + g.vx, py - 3 + g.vy, 1.5, 0, Math.PI*2); ctx.fill();
        });
    }

    function updateGame() {
        if(!gameActive) return;

        // --- MOVIMENTO DO PAC-MAN ---
        let nextX = pac.x + pac.vx;
        let nextY = pac.y + pac.vy;

        if(nextX < 0) nextX = map[0].length - 1;
        if(nextX >= map[0].length) nextX = 0;

        if(map[nextY] && map[nextY][nextX] !== 1) {
            pac.x = nextX; pac.y = nextY;
            if(map[pac.y][pac.x] === 2) {
                map[pac.y][pac.x] = 0;
                score += 10;
                document.getElementById('pacScore').innerText = "Pontuação: " + score;
            }
        }

        // --- MOVIMENTO DOS FANTASMAS (IA Simples) ---
        ghosts.forEach(g => {
            let possibleMoves = [];
            const directions = [{vx:1, vy:0}, {vx:-1, vy:0}, {vx:0, vy:1}, {vx:0, vy:-1}];

            directions.forEach(d => {
                let nx = g.x + d.vx;
                let ny = g.y + d.vy;
                
                if(nx < 0) nx = map[0].length - 1;
                if(nx >= map[0].length) nx = 0;

                // Pode andar se não for parede e evita voltar para trás instantaneamente
                if(map[ny] && map[ny][nx] !== 1) {
                    if (!(d.vx === -g.vx && d.vy === -g.vy)) {
                        possibleMoves.push(d);
                    }
                }
            });

            // Se for um beco sem saída, ele volta
            if (possibleMoves.length === 0) possibleMoves.push({vx: -g.vx, vy: -g.vy});

            // Escolhe uma direção aleatória válida nas encruzilhadas
            let move = possibleMoves[Math.floor(Math.random() * possibleMoves.length)];
            g.vx = move.vx; g.vy = move.vy;
            
            g.x += g.vx; g.y += g.vy;
            if(g.x < 0) g.x = map[0].length - 1;
            if(g.x >= map[0].length) g.x = 0;

            // Colisão com o Pac-Man (GAME OVER)
            if(g.x === pac.x && g.y === pac.y) {
                gameActive = false;
                setTimeout(() => alert("GAME OVER! Os fantasmas te pegaram.\nPontuação final: " + score), 50);
            }
        });

        // Desenhar tudo
        drawMap();
        drawGhosts();

        // Desenhar Pac-Man com a boca na direção certa
        ctx.save();
        ctx.translate(pac.x*tileSize + 10, pac.y*tileSize + 10);
        ctx.rotate(pac.angle);
        ctx.fillStyle = "yellow";
        ctx.beginPath();
        // Animação simples de boca abrindo e fechando
        let mouthOpen = (Date.now() % 300) > 150 ? 0.2 : 0.05; 
        ctx.arc(0, 0, 8, mouthOpen * Math.PI, (2 - mouthOpen) * Math.PI); 
        ctx.lineTo(0, 0);
        ctx.fill();
        ctx.restore();
    }

    // Loop do jogo
    setInterval(updateGame, 180); // Velocidade do jogo (menor = mais rápido)
};
</script>
@endpush