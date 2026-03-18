@extends('layouts.arcade')
@section('title', 'Flappy Bird - Arcade')

@section('content')
<div class="bg-sky-300 p-6 rounded-xl shadow-2xl relative w-full max-w-2xl text-center border-4 border-yellow-400">
    <a href="{{ route('arcade.index') }}" class="absolute -top-4 -right-4 bg-red-600 text-white w-10 h-10 rounded-full font-bold text-xl border-2 border-white hover:bg-red-700 flex items-center justify-center decoration-transparent">&times;</a>
    <h2 class="text-3xl font-black mb-4 text-white drop-shadow-md uppercase">Flappy Bird</h2>
    <p class="text-white font-bold mb-2">Pressione ESPAÇO ou clique no jogo para pular</p>
    
    <canvas id="flappyCanvas" width="400" height="500" class="bg-sky-400 border-b-8 border-green-500 cursor-pointer"></canvas>
</div>
@endsection

@push('scripts')
<script>
window.addEventListener('load', function() {
    const canvas = document.getElementById("flappyCanvas");
    const ctx = canvas.getContext("2d");

    let frames = 0;
    let score = 0;
    let isGameOver = false;

    const bird = {
        x: 50, y: 150, width: 20, height: 20,
        gravity: 0.25, velocity: 0, jump: -5.5,
        draw() {
            ctx.fillStyle = "yellow";
            ctx.fillRect(this.x, this.y, this.width, this.height);
        },
        update() {
            this.velocity += this.gravity;
            this.y += this.velocity;
            if (this.y + this.height >= canvas.height) { this.y = canvas.height - this.height; isGameOver = true; }
            if (this.y <= 0) { this.y = 0; }
        },
        flap() { this.velocity = this.jump; }
    };

    const pipes = {
        position: [], width: 50, gap: 120, dx: 2,
        draw() {
            for (let i = 0; i < this.position.length; i++) {
                let p = this.position[i];
                let topYPos = p.y;
                let bottomYPos = p.y + this.gap;
                ctx.fillStyle = "green";
                ctx.fillRect(p.x, 0, this.width, topYPos); // Cano cima
                ctx.fillRect(p.x, bottomYPos, this.width, canvas.height - bottomYPos); // Cano baixo
            }
        },
        update() {
            if (frames % 100 == 0) {
                this.position.push({ x: canvas.width, y: Math.random() * (canvas.height - this.gap - 50) + 20 });
            }
            for (let i = 0; i < this.position.length; i++) {
                let p = this.position[i];
                p.x -= this.dx;
                // Colisão
                if (bird.x + bird.width > p.x && bird.x < p.x + this.width && (bird.y < p.y || bird.y + bird.height > p.y + this.gap)) {
                    isGameOver = true;
                }
                if (p.x + this.width === bird.x) score++;
                if (p.x + this.width <= 0) this.position.shift(); // Remove canos que saíram da tela
            }
        }
    };

    function reset() { bird.y = 150; bird.velocity = 0; pipes.position = []; score = 0; isGameOver = false; frames = 0; loop(); }

    window.addEventListener("keydown", (e) => { if (e.code === "Space") { if(isGameOver) reset(); else bird.flap(); } });
    canvas.addEventListener("mousedown", () => { if(isGameOver) reset(); else bird.flap(); });

    function loop() {
        if(isGameOver) {
            ctx.fillStyle = "black"; ctx.font = "30px Arial"; ctx.fillText("Game Over! Score: " + score, 50, canvas.height/2);
            ctx.font = "15px Arial"; ctx.fillText("Clique para reiniciar", 130, canvas.height/2 + 30);
            return;
        }
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        bird.draw(); bird.update();
        pipes.draw(); pipes.update();
        ctx.fillStyle = "white"; ctx.font = "30px Arial"; ctx.fillText(score, canvas.width/2, 50);
        frames++;
        requestAnimationFrame(loop);
    }
    loop();
});
</script>
@endpush