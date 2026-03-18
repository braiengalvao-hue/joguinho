@extends('layouts.arcade')
@section('title', 'Free Fire 2D - Arcade')

@section('content')
<div class="bg-gray-800 p-6 rounded-xl shadow-2xl relative w-full max-w-4xl text-center border-4 border-orange-500">
    <a href="{{ route('arcade.index') }}" class="absolute -top-4 -right-4 bg-red-600 text-white w-10 h-10 rounded-full font-bold text-xl border-2 border-white hover:bg-red-700 flex items-center justify-center decoration-transparent z-50">&times;</a>
    
    <div class="flex justify-between text-white font-bold mb-2">
        <span class="text-green-400">HP: <span id="ff-hp">100</span>%</span>
        <h2 class="text-3xl font-black text-orange-500 uppercase tracking-widest italic">FREE FIRE 2D</h2>
        <span class="text-yellow-400">KILLS: <span id="ff-kills">0</span></span>
    </div>
    
    <canvas id="ffCanvas" width="800" height="500" class="bg-green-800 border-4 border-gray-900 mx-auto cursor-crosshair"></canvas>
    <p class="mt-2 text-gray-400">Use <kbd class="bg-gray-700 px-2 rounded">W A S D</kbd> para mover e o <kbd class="bg-gray-700 px-2 rounded">Mouse</kbd> para mirar e atirar!</p>
</div>
@endsection

@push('scripts')
<script>
window.onload = function() {
    const canvas = document.getElementById('ffCanvas');
    const ctx = canvas.getContext('2d');

    let player = { x: 400, y: 250, radius: 15, color: '#3b82f6', speed: 4, hp: 100 };
    let bullets = [];
    let enemies = [];
    let particles = [];
    let keys = {};
    let mouse = { x: 0, y: 0 };
    let kills = 0;
    let gameActive = true;
    let animId;

    // Controles de teclado
    window.addEventListener('keydown', e => keys[e.key.toLowerCase()] = true);
    window.addEventListener('keyup', e => keys[e.key.toLowerCase()] = false);

    // Controles de mouse
    canvas.addEventListener('mousemove', e => {
        const rect = canvas.getBoundingClientRect();
        mouse.x = e.clientX - rect.left;
        mouse.y = e.clientY - rect.top;
    });

    canvas.addEventListener('mousedown', () => {
        if(!gameActive) return;
        const angle = Math.atan2(mouse.y - player.y, mouse.x - player.x);
        const velocity = { x: Math.cos(angle) * 10, y: Math.sin(angle) * 10 };
        bullets.push({ x: player.x, y: player.y, radius: 4, color: '#fbbf24', velocity: velocity });
    });

    // Spawn de Inimigos
    setInterval(() => {
        if(!gameActive) return;
        let x, y;
        if(Math.random() < 0.5) { x = Math.random() < 0.5 ? 0 - 20 : canvas.width + 20; y = Math.random() * canvas.height; } 
        else { x = Math.random() * canvas.width; y = Math.random() < 0.5 ? 0 - 20 : canvas.height + 20; }
        
        const angle = Math.atan2(player.y - y, player.x - x);
        const velocity = { x: Math.cos(angle) * 1.5, y: Math.sin(angle) * 1.5 };
        enemies.push({ x: x, y: y, radius: 14, color: '#ef4444', velocity: velocity, hp: 2 });
    }, 1000);

    function gameLoop() {
        if(!gameActive) return;
        animId = requestAnimationFrame(gameLoop);
        
        // Fundo
        ctx.fillStyle = '#166534'; // Grama
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // Movimento do Player
        if((keys['w'] || keys['arrowup']) && player.y - player.radius > 0) player.y -= player.speed;
        if((keys['s'] || keys['arrowdown']) && player.y + player.radius < canvas.height) player.y += player.speed;
        if((keys['a'] || keys['arrowleft']) && player.x - player.radius > 0) player.x -= player.speed;
        if((keys['d'] || keys['arrowright']) && player.x + player.radius < canvas.width) player.x += player.speed;

        // Desenhar Player
        ctx.beginPath(); ctx.arc(player.x, player.y, player.radius, 0, Math.PI * 2); ctx.fillStyle = player.color; ctx.fill();
        // Arma do player apontando para o mouse
        ctx.save(); ctx.translate(player.x, player.y);
        ctx.rotate(Math.atan2(mouse.y - player.y, mouse.x - player.x));
        ctx.fillStyle = '#1f2937'; ctx.fillRect(0, -4, 25, 8); // Cano da arma
        ctx.restore();

        // Atualizar Tiros
        bullets.forEach((bullet, index) => {
            bullet.x += bullet.velocity.x; bullet.y += bullet.velocity.y;
            ctx.beginPath(); ctx.arc(bullet.x, bullet.y, bullet.radius, 0, Math.PI * 2); ctx.fillStyle = bullet.color; ctx.fill();
            if(bullet.x < 0 || bullet.x > canvas.width || bullet.y < 0 || bullet.y > canvas.height) bullets.splice(index, 1);
        });

        // Atualizar Inimigos
        enemies.forEach((enemy, eIndex) => {
            // Recalcula direção para seguir o player sempre
            const angle = Math.atan2(player.y - enemy.y, player.x - enemy.x);
            enemy.velocity = { x: Math.cos(angle) * 1.5, y: Math.sin(angle) * 1.5 };
            
            enemy.x += enemy.velocity.x; enemy.y += enemy.velocity.y;
            ctx.beginPath(); ctx.arc(enemy.x, enemy.y, enemy.radius, 0, Math.PI * 2); ctx.fillStyle = enemy.color; ctx.fill();

            // Colisão Inimigo com Player
            const dist = Math.hypot(player.x - enemy.x, player.y - enemy.y);
            if(dist - enemy.radius - player.radius < 1) {
                enemies.splice(eIndex, 1);
                player.hp -= 20;
                document.getElementById('ff-hp').innerText = player.hp;
                if(player.hp <= 0) {
                    gameActive = false;
                    setTimeout(() => alert("ELIMINADO! Total de Kills: " + kills), 100);
                }
            }

            // Colisão Tiro com Inimigo
            bullets.forEach((bullet, bIndex) => {
                const dist = Math.hypot(bullet.x - enemy.x, bullet.y - enemy.y);
                if(dist - enemy.radius - bullet.radius < 1) {
                    setTimeout(() => {
                        enemies.splice(eIndex, 1);
                        bullets.splice(bIndex, 1);
                        kills++;
                        document.getElementById('ff-kills').innerText = kills;
                    }, 0);
                }
            });
        });
    }

    gameLoop();
};
</script>
@endpush