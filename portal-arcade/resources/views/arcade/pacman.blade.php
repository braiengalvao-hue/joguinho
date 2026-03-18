@extends('layouts.arcade')
@section('title', 'Pacman - Arcade')

@section('content')
<div class="bg-black p-6 rounded-xl shadow-2xl relative w-full max-w-2xl text-center border-4 border-blue-800">
    <a href="{{ route('arcade.index') }}" class="absolute -top-4 -right-4 bg-red-600 text-white w-10 h-10 rounded-full font-bold text-xl border-2 border-white hover:bg-red-700 flex items-center justify-center decoration-transparent">&times;</a>
    <h2 class="text-3xl font-black mb-4 text-yellow-400 uppercase">Pac-Man</h2>
    
    <canvas id="pacmanCanvas" width="400" height="400" class="bg-black border-2 border-blue-900"></canvas>
    <p id="pacScore" class="text-white font-mono mt-4 text-xl">Pontuação: 0</p>
</div>
@endsection

@push('scripts')
<script>
window.onload = function() {
    const canvas = document.getElementById('pacmanCanvas');
    const ctx = canvas.getContext('2d');
    const tileSize = 20;
    let score = 0;
    
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

    let pac = { x: 9, y: 15, vx: 0, vy: 0 }; // Posição no grid (índice)

    document.addEventListener("keydown", e => {
        if(e.key === "ArrowLeft") { pac.vx = -1; pac.vy = 0; }
        if(e.key === "ArrowUp") { pac.vx = 0; pac.vy = -1; }
        if(e.key === "ArrowRight") { pac.vx = 1; pac.vy = 0; }
        if(e.key === "ArrowDown") { pac.vx = 0; pac.vy = 1; }
    });

    function drawMap() {
        ctx.fillStyle = "black"; ctx.fillRect(0, 0, canvas.width, canvas.height);
        for(let r = 0; r < map.length; r++){
            for(let c = 0; c < map[r].length; c++){
                if(map[r][c] === 1) { ctx.fillStyle = "blue"; ctx.fillRect(c*tileSize, r*tileSize, tileSize, tileSize); }
                else if(map[r][c] === 2) { 
                    ctx.fillStyle = "white"; 
                    ctx.beginPath(); ctx.arc(c*tileSize + 10, r*tileSize + 10, 3, 0, Math.PI*2); ctx.fill();
                }
            }
        }
    }

    function updatePac() {
        let nextX = pac.x + pac.vx;
        let nextY = pac.y + pac.vy;

        // Efeito túnel (atravessar a tela)
        if(nextX < 0) nextX = map[0].length - 1;
        if(nextX >= map[0].length) nextX = 0;

        // Só move se não for parede
        if(map[nextY] && map[nextY][nextX] !== 1) {
            pac.x = nextX; pac.y = nextY;
            if(map[pac.y][pac.x] === 2) {
                map[pac.y][pac.x] = 0; // Comeu a bolinha
                score += 10;
                document.getElementById('pacScore').innerText = "Pontuação: " + score;
            }
        }

        ctx.fillStyle = "yellow";
        ctx.beginPath();
        ctx.arc(pac.x*tileSize + 10, pac.y*tileSize + 10, 8, 0.2 * Math.PI, 1.8 * Math.PI); // Boca aberta
        ctx.lineTo(pac.x*tileSize + 10, pac.y*tileSize + 10);
        ctx.fill();
    }

    setInterval(() => {
        drawMap();
        updatePac();
    }, 150); // Velocidade do jogo
};
</script>
@endpush