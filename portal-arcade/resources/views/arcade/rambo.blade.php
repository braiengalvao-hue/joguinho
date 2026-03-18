@extends('layouts.arcade')
@section('title', 'Ação Frenética - Arcade')

@section('content')
<div class="bg-gray-900 p-6 rounded-xl shadow-2xl relative w-full max-w-4xl text-center border-4 border-red-700">
    <a href="{{ route('arcade.index') }}" class="absolute -top-4 -right-4 bg-red-600 text-white w-10 h-10 rounded-full font-bold text-xl border-2 border-white hover:bg-red-700 flex items-center justify-center decoration-transparent z-50">&times;</a>
    
    <div class="flex justify-between items-center mb-2 px-4 text-white font-bold font-mono">
        <div class="text-green-400 text-xl">HP: <span id="player-hp">100</span>%</div>
        <h2 class="text-3xl font-black text-red-500 uppercase tracking-widest italic drop-shadow-[0_0_8px_rgba(220,38,38,0.8)]">Comando 2D</h2>
        <div class="text-yellow-400 text-xl">KILLS: <span id="kill-count">0</span></div>
    </div>
    
    <div id="boss-ui" class="hidden w-full px-4 mb-2">
        <p class="text-red-500 font-black animate-pulse">CHEFÃO ALERTA!</p>
        <div class="w-full h-4 bg-gray-700 border-2 border-red-500">
            <div id="boss-hp-bar" class="h-full bg-red-500 w-full transition-all"></div>
        </div>
    </div>

    <canvas id="gameCanvas" width="800" height="400" class="bg-slate-800 border-b-8 border-yellow-700 mx-auto rounded shadow-inner"></canvas>
    
    <div class="mt-4 text-gray-300 font-bold bg-gray-800 p-2 rounded inline-block border border-gray-600">
        Controles: <kbd class="bg-gray-700 px-2 rounded text-white">SETAS</kbd> Mover &middot; <kbd class="bg-gray-700 px-2 rounded text-white">Z</kbd> Pular &middot; <kbd class="bg-gray-700 px-2 rounded text-white">X</kbd> Atirar
    </div>
</div>
@endsection

@push('scripts')
<script>
window.onload = function() {
    const canvas = document.getElementById('gameCanvas');
    const ctx = canvas.getContext('2d');

    // Estados do jogo
    let gameActive = true;
    let kills = 0;
    let bossActive = false;
    let keys = {};

    // Entidades
    const gravity = 0.6;
    const groundY = 350;

    let player = {
        x: 100, y: groundY - 40, width: 30, height: 40,
        vx: 0, vy: 0, speed: 5, jumpForce: -12,
        hp: 100, color: '#3b82f6', facingRight: true,
        cooldown: 0
    };

    let bullets = [];
    let enemies = [];
    let enemyBullets = [];

    let boss = {
        x: 850, y: groundY - 100, width: 100, height: 100,
        vy: 2, hp: 200, maxHp: 200, color: '#991b1b', shootTimer: 0
    };

    // Controles
    window.addEventListener('keydown', e => keys[e.code] = true);
    window.addEventListener('keyup', e => keys[e.code] = false);

    function spawnEnemy() {
        if(!gameActive || bossActive) return;
        enemies.push({
            x: canvas.width, y: groundY - 40, width: 30, height: 40,
            vx: -2 - (Math.random() * 2), hp: 20, color: '#ef4444'
        });
    }

    // Spawn de inimigos normais a cada 1.5s
    let spawner = setInterval(spawnEnemy, 1500);

    function checkCollision(r1, r2) {
        return r1.x < r2.x + r2.width && r1.x + r1.width > r2.x &&
               r1.y < r2.y + r2.height && r1.y + r1.height > r2.y;
    }

    function gameLoop() {
        if(!gameActive) return;
        requestAnimationFrame(gameLoop);

        // Fundo e Chão
        ctx.fillStyle = '#1e293b'; ctx.fillRect(0, 0, canvas.width, canvas.height); // Céu noturno
        ctx.fillStyle = '#422006'; ctx.fillRect(0, groundY, canvas.width, canvas.height - groundY); // Terra

        // --- MOVIMENTO DO JOGADOR ---
        if (keys['ArrowLeft']) { player.vx = -player.speed; player.facingRight = false; }
        else if (keys['ArrowRight']) { player.vx = player.speed; player.facingRight = true; }
        else { player.vx = 0; }

        if (keys['KeyZ'] && player.y + player.height >= groundY) { player.vy = player.jumpForce; }

        player.vy += gravity;
        player.x += player.vx;
        player.y += player.vy;

        // Limites da tela pro jogador
        if (player.x < 0) player.x = 0;
        if (player.x + player.width > canvas.width) player.x = canvas.width - player.width;
        if (player.y + player.height > groundY) {
            player.y = groundY - player.height;
            player.vy = 0;
        }

        // --- ATIRAR ---
        if (player.cooldown > 0) player.cooldown--;
        if (keys['KeyX'] && player.cooldown <= 0) {
            bullets.push({
                x: player.facingRight ? player.x + player.width : player.x - 10,
                y: player.y + 10, width: 10, height: 4, color: '#facc15',
                vx: player.facingRight ? 10 : -10
            });
            player.cooldown = 15; // Velocidade de tiro
        }

        // --- DESENHAR JOGADOR ---
        ctx.fillStyle = player.color;
        ctx.fillRect(player.x, player.y, player.width, player.height);
        // "Arma" do jogador
        ctx.fillStyle = '#9ca3af';
        if(player.facingRight) ctx.fillRect(player.x + 15, player.y + 10, 20, 6);
        else ctx.fillRect(player.x - 5, player.y + 10, 20, 6);

        // --- ATUALIZAR TIROS ---
        for (let i = bullets.length - 1; i >= 0; i--) {
            let b = bullets[i];
            b.x += b.vx;
            ctx.fillStyle = b.color;
            ctx.fillRect(b.x, b.y, b.width, b.height);
            if (b.x < 0 || b.x > canvas.width) bullets.splice(i, 1);
        }

        // --- ATUALIZAR INIMIGOS NORMAIS ---
        for (let i = enemies.length - 1; i >= 0; i--) {
            let e = enemies[i];
            e.x += e.vx;
            ctx.fillStyle = e.color;
            ctx.fillRect(e.x, e.y, e.width, e.height);

            // Colisão Inimigo x Jogador
            if (checkCollision(player, e)) {
                player.hp -= 10;
                enemies.splice(i, 1);
                document.getElementById('player-hp').innerText = Math.max(0, player.hp);
                continue;
            }

            // Colisão Tiro x Inimigo
            for (let j = bullets.length - 1; j >= 0; j--) {
                if (checkCollision(bullets[j], e)) {
                    e.hp -= 10;
                    bullets.splice(j, 1);
                    if (e.hp <= 0) {
                        enemies.splice(i, 1);
                        kills++;
                        document.getElementById('kill-count').innerText = kills;
                        // Aciona o Boss
                        if(kills === 15) { bossActive = true; document.getElementById('boss-ui').classList.remove('hidden'); }
                        break;
                    }
                }
            }
        }

        // --- LÓGICA DO CHEFÃO ---
        if (bossActive) {
            // Boss entra na tela
            if (boss.x > canvas.width - boss.width - 20) boss.x -= 2;
            else {
                // Boss flutua pra cima e pra baixo
                boss.y += boss.vy;
                if (boss.y <= 50 || boss.y + boss.height >= groundY) boss.vy *= -1;

                // Boss atira
                boss.shootTimer++;
                if (boss.shootTimer > 60) {
                    enemyBullets.push({
                        x: boss.x, y: boss.y + boss.height/2, width: 20, height: 10,
                        vx: -6, color: '#f97316'
                    });
                    boss.shootTimer = 0;
                }
            }

            // Desenha Boss
            ctx.fillStyle = boss.color;
            ctx.fillRect(boss.x, boss.y, boss.width, boss.height);
            // Olho/Canhão do boss
            ctx.fillStyle = 'black';
            ctx.fillRect(boss.x - 10, boss.y + boss.height/2 - 10, 30, 20);

            // Colisão Tiro do Jogador x Boss
            for (let j = bullets.length - 1; j >= 0; j--) {
                if (checkCollision(bullets[j], boss)) {
                    boss.hp -= 10;
                    bullets.splice(j, 1);
                    document.getElementById('boss-hp-bar').style.width = (boss.hp / boss.maxHp * 100) + '%';
                    
                    if (boss.hp <= 0) {
                        gameActive = false;
                        setTimeout(() => alert("🎆 VITÓRIA! VOCÊ DESTRUIU O TANQUE GIGANTE!"), 100);
                    }
                }
            }
        }

        // --- TIROS INIMIGOS (Do Boss) ---
        for (let i = enemyBullets.length - 1; i >= 0; i--) {
            let eb = enemyBullets[i];
            eb.x += eb.vx;
            ctx.fillStyle = eb.color;
            ctx.fillRect(eb.x, eb.y, eb.width, eb.height);

            if (checkCollision(player, eb)) {
                player.hp -= 15;
                enemyBullets.splice(i, 1);
                document.getElementById('player-hp').innerText = Math.max(0, player.hp);
            } else if (eb.x < 0) {
                enemyBullets.splice(i, 1);
            }
        }

        // --- GAME OVER ---
        if (player.hp <= 0) {
            gameActive = false;
            setTimeout(() => alert("💀 GAME OVER! Você morreu em combate."), 100);
        }
    }

    gameLoop();
};
</script>
@endpush