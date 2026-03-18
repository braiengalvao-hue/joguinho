import os
import subprocess

PROJECT_NAME = "portal-arcade"

def run_command(command, cwd=None):
    print(f"Executando: {command}")
    subprocess.run(command, shell=True, check=True, cwd=cwd)

def update_blade():
    blade_code = """<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Arcade Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <style>
        .game-card:hover { transform: scale(1.05); transition: all 0.2s; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3); }
        canvas { display: block; margin: 0 auto; background: #000; border-radius: 4px; border: 2px solid #333; }
        
        /* Jogo da Memória - Efeitos 3D */
        .memory-card { width: 70px; height: 70px; position: relative; transform-style: preserve-3d; transition: transform 0.5s; cursor: pointer; }
        .memory-card.flip { transform: rotateY(180deg); }
        .memory-card .front, .memory-card .back { width: 100%; height: 100%; position: absolute; backface-visibility: hidden; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .memory-card .front { background: linear-gradient(135deg, #6366f1, #a855f7); transform: rotateY(0deg); border: 2px solid #fff; } /* Costas (padrão) */
        .memory-card .back { background: #fff; transform: rotateY(180deg); border: 2px solid #6366f1; } /* Face do emoji */
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 transition-colors duration-300 min-h-screen">

    <div class="flex justify-between items-center p-4 bg-indigo-600 text-white shadow-lg">
        <h1 class="text-3xl font-bold tracking-wider">🎮 ARCADE PORTAL</h1>
        <div class="flex items-center gap-6">
            <div class="bg-indigo-800 px-4 py-2 rounded-lg font-mono text-lg shadow-inner">
                💰 <span id="moedas-display" class="text-yellow-400 font-bold">0</span> | 
                🌟 <span id="xp-display" class="text-blue-300 font-bold">0</span>
            </div>
            <button onclick="toggleTheme()" class="bg-gray-800 p-2 rounded-full hover:bg-gray-700">🌙/☀️</button>
        </div>
    </div>

    <div class="container mx-auto p-8 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
        
        <div onclick="openGame('pacman')" class="game-card bg-yellow-400 rounded-xl h-32 flex items-center justify-center cursor-pointer text-gray-900 font-black text-2xl shadow-md border-4 border-black">ᗧ···ᗣ Pac-Man</div>
        <div onclick="openGame('memory')" class="game-card bg-pink-500 rounded-xl h-32 flex items-center justify-center cursor-pointer text-white font-bold text-xl shadow-md">🃏 Memória</div>

        <div class="bg-green-500 rounded-xl h-32 flex items-center justify-center text-white font-bold text-xl opacity-60">🐍 Snake</div>
        <div class="bg-blue-500 rounded-xl h-32 flex items-center justify-center text-white font-bold text-xl opacity-60">❌ Velha</div>
        <div class="bg-teal-500 rounded-xl h-32 flex items-center justify-center text-white font-bold text-xl opacity-60">🏏 Breakout</div>
        <div class="bg-gray-500 rounded-xl h-32 flex items-center justify-center text-white font-bold text-xl opacity-60">💣 Minas</div>
        
        <div class="bg-blue-800 rounded-xl h-32 flex items-center justify-center opacity-40 text-white font-bold">Sudoku (Em Breve)</div>
        <div class="bg-green-700 rounded-xl h-32 flex items-center justify-center opacity-40 text-white font-bold">Dino Run (Em Breve)</div>
    </div>

    <div id="game-modal" class="fixed inset-0 bg-black bg-opacity-80 hidden flex flex-col items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-2xl relative w-11/12 max-w-2xl text-center border-4 border-indigo-500">
            <button onclick="closeGame()" class="absolute -top-4 -right-4 bg-red-600 text-white w-10 h-10 rounded-full font-bold text-xl border-2 border-white hover:bg-red-700 shadow-lg">&times;</button>
            <h2 id="modal-title" class="text-3xl font-black mb-4 dark:text-white uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Game</h2>
            <div id="game-container" class="w-full h-full flex justify-center flex-col items-center"></div>
        </div>
    </div>

    <script>
        let currentGameInterval = null;

        function toggleTheme() { document.documentElement.classList.toggle('dark'); }

        function sendReward(xp, moedas) {
            fetch('/reward', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                body: JSON.stringify({ nickname: 'Jogador1', xp: xp, moedas: moedas })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    const xpEl = document.getElementById('xp-display');
                    const moedasEl = document.getElementById('moedas-display');
                    xpEl.innerText = data.xp;
                    moedasEl.innerText = data.moedas;
                    xpEl.parentElement.classList.add('bg-green-600');
                    setTimeout(() => xpEl.parentElement.classList.remove('bg-green-600'), 300);
                }
            });
        }

        function openGame(gameId) {
            document.getElementById('game-modal').classList.remove('hidden');
            const container = document.getElementById('game-container');
            container.innerHTML = ''; 
            document.onkeydown = null;
            
            if (gameId === 'pacman') playPacman(container);
            if (gameId === 'memory') playMemory(container);
        }

        function closeGame() {
            document.getElementById('game-modal').classList.add('hidden');
            if(currentGameInterval) clearInterval(currentGameInterval);
            document.onkeydown = null;
        }

        // --- 13. JOGO: PAC-MAN (Versão Simplificada em Grid) ---
        function playPacman(container) {
            document.getElementById('modal-title').innerText = "Pac-Man";
            container.innerHTML = `
                <div class="flex justify-between w-[300px] mb-2 font-bold dark:text-white text-xl">
                    <span>Score: <span id="pac-score" class="text-yellow-500">0</span></span>
                </div>
                <canvas id="pacCanvas" width="300" height="300"></canvas>
                <p class="mt-4 text-gray-500 dark:text-gray-400 bg-gray-200 dark:bg-gray-700 py-1 px-4 rounded-full">Use as SETAS para fugir dos fantasmas!</p>
            `;
            const canvas = document.getElementById('pacCanvas');
            const ctx = canvas.getContext('2d');
            const TS = 20; // Tile Size
            let score = 0;

            // 1=Parede, 0=Pílula, 2=Vazio
            let map = [
                [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],
                [1,0,0,0,0,0,0,1,0,0,0,0,0,0,1],
                [1,0,1,1,1,1,0,1,0,1,1,1,1,0,1],
                [1,0,0,0,0,0,0,0,0,0,0,0,0,0,1],
                [1,0,1,1,0,1,1,1,1,1,0,1,1,0,1],
                [1,0,0,0,0,0,0,1,0,0,0,0,0,0,1],
                [1,1,1,1,1,1,0,1,0,1,1,1,1,1,1],
                [2,2,2,2,2,1,0,0,0,1,2,2,2,2,2],
                [1,1,1,1,1,1,0,1,0,1,1,1,1,1,1],
                [1,0,0,0,0,0,0,1,0,0,0,0,0,0,1],
                [1,0,1,1,0,1,1,1,1,1,0,1,1,0,1],
                [1,0,0,0,0,0,0,0,0,0,0,0,0,0,1],
                [1,0,1,1,1,1,0,1,0,1,1,1,1,0,1],
                [1,0,0,0,0,0,0,1,0,0,0,0,0,0,1],
                [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1]
            ];

            let pac = { x: 7, y: 11, dx: 0, dy: 0, nextDx: 0, nextDy: 0, open: 0 };
            let ghosts = [
                { x: 1, y: 1, color: "red", dx: 1, dy: 0 },
                { x: 13, y: 1, color: "pink", dx: -1, dy: 0 },
                { x: 13, y: 13, color: "cyan", dx: -1, dy: 0 }
            ];

            document.onkeydown = (e) => {
                if(["ArrowUp","ArrowDown","ArrowLeft","ArrowRight"].includes(e.key)) e.preventDefault();
                if(e.key === "ArrowLeft") { pac.nextDx = -1; pac.nextDy = 0; }
                if(e.key === "ArrowRight") { pac.nextDx = 1; pac.nextDy = 0; }
                if(e.key === "ArrowUp") { pac.nextDx = 0; pac.nextDy = -1; }
                if(e.key === "ArrowDown") { pac.nextDx = 0; pac.nextDy = 1; }
            };

            function drawMap() {
                ctx.fillStyle = "black"; ctx.fillRect(0,0,300,300);
                for(let r=0; r<15; r++) {
                    for(let c=0; c<15; c++) {
                        if(map[r][c] === 1) {
                            ctx.fillStyle = "#1e3a8a"; // Parede Azul Escura
                            ctx.fillRect(c*TS, r*TS, TS, TS);
                            ctx.strokeStyle = "#3b82f6";
                            ctx.strokeRect(c*TS+2, r*TS+2, TS-4, TS-4);
                        } else if(map[r][c] === 0) {
                            ctx.fillStyle = "#fbbf24"; // Pílula amarela
                            ctx.beginPath(); ctx.arc(c*TS + TS/2, r*TS + TS/2, 3, 0, Math.PI*2); ctx.fill();
                        }
                    }
                }
            }

            function getValidDirections(x, y) {
                let dirs = [];
                if(map[y][x+1] !== 1) dirs.push({dx: 1, dy: 0});
                if(map[y][x-1] !== 1) dirs.push({dx: -1, dy: 0});
                if(map[y+1][x] !== 1) dirs.push({dx: 0, dy: 1});
                if(map[y-1][x] !== 1) dirs.push({dx: 0, dy: -1});
                return dirs;
            }

            function gameStep() {
                // Tenta aplicar a próxima direção do jogador
                if(map[pac.y + pac.nextDy] && map[pac.y + pac.nextDy][pac.x + pac.nextDx] !== 1) {
                    pac.dx = pac.nextDx; pac.dy = pac.nextDy;
                }
                
                // Move Pac-Man se não for bater na parede
                if(map[pac.y + pac.dy] && map[pac.y + pac.dy][pac.x + pac.dx] !== 1) {
                    pac.x += pac.dx; pac.y += pac.dy;
                    // Lógica de Túnel (saiu pela esquerda, volta pela direita)
                    if(pac.x < 0) pac.x = 14;
                    if(pac.x > 14) pac.x = 0;
                }

                // Come pílula
                if(map[pac.y][pac.x] === 0) {
                    map[pac.y][pac.x] = 2;
                    score += 10;
                    document.getElementById('pac-score').innerText = score;
                }

                // Move Fantasmas
                ghosts.forEach(g => {
                    let dirs = getValidDirections(g.x, g.y);
                    // Evitar que o fantasma volte para trás a menos que seja um beco sem saída
                    let forwardDirs = dirs.filter(d => !(d.dx === -g.dx && d.dy === -g.dy));
                    if(forwardDirs.length > 0) {
                        let pick = forwardDirs[Math.floor(Math.random() * forwardDirs.length)];
                        g.dx = pick.dx; g.dy = pick.dy;
                    } else if(dirs.length > 0) {
                        g.dx = dirs[0].dx; g.dy = dirs[0].dy;
                    }
                    g.x += g.dx; g.y += g.dy;
                    if(g.x < 0) g.x = 14; if(g.x > 14) g.x = 0;
                });

                drawMap();

                // Desenha Fantasmas
                ghosts.forEach(g => {
                    ctx.fillStyle = g.color;
                    ctx.beginPath();
                    ctx.arc(g.x*TS + TS/2, g.y*TS + TS/2, TS/2.2, Math.PI, 0); // Cabeça arredondada
                    ctx.lineTo(g.x*TS + TS, g.y*TS + TS);
                    ctx.lineTo(g.x*TS, g.y*TS + TS);
                    ctx.fill();
                    // Olhos
                    ctx.fillStyle = "white"; ctx.beginPath(); ctx.arc(g.x*TS + 6, g.y*TS + 8, 3, 0, Math.PI*2); ctx.fill();
                    ctx.beginPath(); ctx.arc(g.x*TS + 14, g.y*TS + 8, 3, 0, Math.PI*2); ctx.fill();
                });

                // Desenha Pac-Man (Animação de boca simples)
                pac.open = !pac.open;
                ctx.fillStyle = "yellow";
                ctx.beginPath();
                let angle = 0;
                if(pac.dx===1) angle=0; if(pac.dx===-1) angle=Math.PI;
                if(pac.dy===1) angle=Math.PI/2; if(pac.dy===-1) angle=-Math.PI/2;
                
                let startAngle = pac.open ? angle + 0.2 * Math.PI : angle + 0.05 * Math.PI;
                let endAngle = pac.open ? angle - 0.2 * Math.PI : angle - 0.05 * Math.PI;
                
                ctx.arc(pac.x*TS + TS/2, pac.y*TS + TS/2, TS/2.2, startAngle, endAngle);
                ctx.lineTo(pac.x*TS + TS/2, pac.y*TS + TS/2);
                ctx.fill();

                // Checa Colisão (Morte)
                let dead = ghosts.some(g => g.x === pac.x && g.y === pac.y);
                if(dead) {
                    clearInterval(currentGameInterval);
                    setTimeout(() => {
                        alert("Waka Waka! Você foi pego! Score: " + score);
                        sendReward(score, Math.floor(score/5));
                        closeGame();
                    }, 50);
                    return;
                }
                
                // Checa Vitória
                let won = true;
                for(let r=0; r<15; r++) for(let c=0; c<15; c++) if(map[r][c]===0) won=false;
                if(won) {
                    clearInterval(currentGameInterval);
                    setTimeout(() => {
                        alert("Parabéns! Você limpou o labirinto!");
                        sendReward(score + 200, 100);
                        closeGame();
                    }, 50);
                }
            }
            
            // Loop do jogo atualizado a cada 200ms (movimentação em grid parecendo arcade antigo)
            currentGameInterval = setInterval(gameStep, 200); 
        }

        // --- 14. JOGO: MEMÓRIA (Com Efeito 3D CSS) ---
        function playMemory(container) {
            document.getElementById('modal-title').innerText = "Jogo da Memória";
            container.innerHTML = `
                <div class="mb-4 flex justify-between w-[320px] font-bold text-xl dark:text-white">
                    <span>Erros: <span id="mem-mistakes" class="text-red-500">0</span></span>
                    <span>Pares: <span id="mem-matches" class="text-green-500">0</span>/8</span>
                </div>
                <div id="memory-grid" class="grid grid-cols-4 gap-4 p-4 bg-gray-200 dark:bg-gray-700 rounded-xl shadow-inner"></div>
            `;
            
            const emojis = ['🚀','👽','👾','🤖','🛸','☄️','🌕','⭐'];
            let cards = [...emojis, ...emojis].sort(() => 0.5 - Math.random());
            const grid = document.getElementById('memory-grid');
            
            let hasFlippedCard = false, lockBoard = false;
            let firstCard, secondCard;
            let matches = 0, mistakes = 0;

            function flipCard() {
                if (lockBoard || this === firstCard) return;
                this.classList.add('flip');

                if (!hasFlippedCard) {
                    hasFlippedCard = true; firstCard = this; return;
                }
                
                secondCard = this;
                checkForMatch();
            }

            function checkForMatch() {
                let isMatch = firstCard.dataset.emoji === secondCard.dataset.emoji;
                if(isMatch) {
                    matches++;
                    document.getElementById('mem-matches').innerText = matches;
                    disableCards();
                } else {
                    mistakes++;
                    document.getElementById('mem-mistakes').innerText = mistakes;
                    unflipCards();
                }
            }

            function disableCards() {
                firstCard.removeEventListener('click', flipCard);
                secondCard.removeEventListener('click', flipCard);
                
                // Efeito visual de acerto
                firstCard.querySelector('.back').style.borderColor = "#22c55e";
                secondCard.querySelector('.back').style.borderColor = "#22c55e";

                if(matches === emojis.length) {
                    let xp = 200 - (mistakes * 10);
                    if(xp < 50) xp = 50;
                    setTimeout(() => { 
                        alert(`Vitória! Você encontrou tudo com ${mistakes} erros.`); 
                        sendReward(xp, Math.floor(xp/4)); 
                        closeGame(); 
                    }, 600);
                }
                resetBoard();
            }

            function unflipCards() {
                lockBoard = true;
                firstCard.querySelector('.back').style.borderColor = "#ef4444";
                secondCard.querySelector('.back').style.borderColor = "#ef4444";
                
                setTimeout(() => {
                    firstCard.classList.remove('flip');
                    secondCard.classList.remove('flip');
                    firstCard.querySelector('.back').style.borderColor = "#6366f1";
                    secondCard.querySelector('.back').style.borderColor = "#6366f1";
                    resetBoard();
                }, 1000); // 1 segundo para memorizar o erro
            }

            function resetBoard() { [hasFlippedCard, lockBoard] = [false, false]; [firstCard, secondCard] = [null, null]; }

            // Criar Cartas na Tela
            cards.forEach(emoji => {
                let card = document.createElement('div');
                card.className = 'memory-card shadow-lg';
                card.dataset.emoji = emoji;
                
                // Face oculta (front do CSS) e Face Revelada (back do CSS)
                card.innerHTML = `
                    <div class="front">❓</div>
                    <div class="back">${emoji}</div>
                `;
                card.addEventListener('click', flipCard);
                grid.appendChild(card);
            });
        }
    </script>
</body>
</html>
"""
    blade_path = os.path.join(PROJECT_NAME, "resources", "views", "friv.blade.php")
    with open(blade_path, "w", encoding='utf-8') as f:
        f.write(blade_code)

if __name__ == "__main__":
    print("🔧 Atualizando Portal Arcade (Add: Pac-Man e Jogo da Memoria)...")
    update_blade()
    print("✅ Novos jogos injetados com sucesso!")
    print("🚀 Iniciando o servidor...")
    try:
        run_command("php artisan serve --host=0.0.0.0", cwd=PROJECT_NAME)
    except KeyboardInterrupt:
        print("\nServidor parado.")