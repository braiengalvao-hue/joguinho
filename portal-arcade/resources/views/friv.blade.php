<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Arcade Portal Completo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <style>
        .game-card:hover { transform: scale(1.05); transition: all 0.2s; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.3); }
        canvas { display: block; margin: 0 auto; background: #111; border-radius: 4px; border: 2px solid #333; }
        .memory-card { width: 60px; height: 60px; position: relative; transform-style: preserve-3d; transition: transform 0.4s; cursor: pointer; }
        .memory-card.flip { transform: rotateY(180deg); }
        .memory-card .front, .memory-card .back { width: 100%; height: 100%; position: absolute; backface-visibility: hidden; display: flex; align-items: center; justify-content: center; font-size: 2rem; border-radius: 8px; }
        .memory-card .front { background: #4f46e5; transform: rotateY(0deg); }
        .memory-card .back { background: #fff; transform: rotateY(180deg); border: 2px solid #4f46e5; }
        .mine-cell { width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; font-weight: bold; cursor: pointer; background-color: #cbd5e1; border: 2px outset #f8fafc; }
        .mine-cell.revealed { background-color: #e2e8f0; border: 1px solid #94a3b8; }
        .simon-btn { filter: brightness(0.5); transition: 0.1s; } .simon-btn.active { filter: brightness(1.5); transform: scale(1.05); }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 transition-colors duration-300 min-h-screen">
    <div class="flex justify-between items-center p-4 bg-indigo-600 text-white shadow-lg">
        <h1 class="text-3xl font-bold tracking-wider">🎮 ARCADE PORTAL</h1>
        <div class="flex items-center gap-6">
            <div class="bg-indigo-800 px-4 py-2 rounded-lg font-mono text-lg shadow-inner">💰 <span id="moedas-display" class="text-yellow-400 font-bold">0</span> | 🌟 <span id="xp-display" class="text-blue-300 font-bold">0</span></div>
            <button onclick="document.documentElement.classList.toggle('dark')" class="bg-gray-800 p-2 rounded-full hover:bg-gray-700">🌙/☀️</button>
        </div>
    </div>

    <div class="container mx-auto p-8 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <div onclick="openGame('snake')" class="game-card bg-green-500 rounded-xl h-24 flex items-center justify-center cursor-pointer text-white font-bold text-lg shadow">🐍 Snake</div>
        <div onclick="openGame('tictactoe')" class="game-card bg-blue-500 rounded-xl h-24 flex items-center justify-center cursor-pointer text-white font-bold text-lg shadow">❌ Velha</div>
        <div onclick="openGame('flappy')" class="game-card bg-yellow-400 rounded-xl h-24 flex items-center justify-center cursor-pointer text-white font-bold text-lg shadow">🐦 Flappy</div>
        <div onclick="openGame('jokempo')" class="game-card bg-red-500 rounded-xl h-24 flex items-center justify-center cursor-pointer text-white font-bold text-lg shadow">✊ Jokempo</div>
        <div onclick="openGame('simon')" class="game-card bg-purple-500 rounded-xl h-24 flex items-center justify-center cursor-pointer text-white font-bold text-lg shadow">🧠 Simon</div>
        <div onclick="openGame('g2048')" class="game-card bg-orange-500 rounded-xl h-24 flex items-center justify-center cursor-pointer text-white font-bold text-lg shadow">🔢 2048</div>
        <div onclick="openGame('breakout')" class="game-card bg-teal-500 rounded-xl h-24 flex items-center justify-center cursor-pointer text-white font-bold text-lg shadow">🏏 Breakout</div>
        <div onclick="openGame('invaders')" class="game-card bg-black rounded-xl h-24 flex items-center justify-center cursor-pointer text-white font-bold text-lg shadow border border-gray-700">👾 Invaders</div>
        <div onclick="openGame('mines')" class="game-card bg-gray-500 rounded-xl h-24 flex items-center justify-center cursor-pointer text-white font-bold text-lg shadow">💣 Minas</div>
        <div onclick="openGame('hangman')" class="game-card bg-yellow-700 rounded-xl h-24 flex items-center justify-center cursor-pointer text-white font-bold text-lg shadow">🪢 Forca</div>
        <div onclick="openGame('pacman')" class="game-card bg-yellow-300 rounded-xl h-24 flex items-center justify-center cursor-pointer text-gray-900 font-black text-xl shadow border-2 border-black">ᗧ···ᗣ Pacman</div>
        <div onclick="openGame('memory')" class="game-card bg-pink-500 rounded-xl h-24 flex items-center justify-center cursor-pointer text-white font-bold text-lg shadow">🃏 Memória</div>
    </div>

    <div id="game-modal" class="fixed inset-0 bg-black bg-opacity-80 hidden flex flex-col items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-2xl relative w-11/12 max-w-2xl text-center border-4 border-indigo-500">
            <button onclick="closeGame()" class="absolute -top-4 -right-4 bg-red-600 text-white w-10 h-10 rounded-full font-bold text-xl border-2 border-white hover:bg-red-700">&times;</button>
            <h2 id="modal-title" class="text-3xl font-black mb-4 dark:text-white uppercase text-indigo-600 dark:text-indigo-400">Game</h2>
            <div id="game-container" class="w-full h-full flex justify-center flex-col items-center"></div>
        </div>
    </div>

    <script>
        let loopInt = null, animReq = null;

        function sendReward(xp, moedas) {
            let xpEl = document.getElementById('xp-display'); let mEl = document.getElementById('moedas-display');
            xpEl.innerText = parseInt(xpEl.innerText) + xp; mEl.innerText = parseInt(mEl.innerText) + moedas;
        }

        function closeGame() {
            document.getElementById('game-modal').classList.add('hidden');
            if(loopInt) clearInterval(loopInt); if(animReq) cancelAnimationFrame(animReq);
            document.onkeydown = null; document.onkeyup = null;
        }

        function openGame(id) {
            document.getElementById('game-modal').classList.remove('hidden');
            const c = document.getElementById('game-container'); c.innerHTML = '';
            document.onkeydown = null; document.onkeyup = null;
            if(loopInt) clearInterval(loopInt); if(animReq) cancelAnimationFrame(animReq);
            
            if(id==='snake') playSnake(c);
            else if(id==='tictactoe') playTicTacToe(c);
            else if(id==='flappy') playFlappy(c);
            else if(id==='jokempo') playJokempo(c);
            else if(id==='simon') playSimon(c);
            else if(id==='g2048') play2048(c);
            else if(id==='breakout') playBreakout(c);
            else if(id==='invaders') playInvaders(c);
            else if(id==='mines') playMines(c);
            else if(id==='hangman') playHangman(c);
            else if(id==='pacman') playPacman(c);
            else if(id==='memory') playMemory(c);
        }

        // 1. SNAKE
        function playSnake(c) {
            document.getElementById('modal-title').innerText = "Snake";
            c.innerHTML = '<canvas id="gc" width="400" height="400"></canvas>';
            let ctx = document.getElementById('gc').getContext('2d');
            let px=10, py=10, gs=20, tc=20, ax=15, ay=15, xv=0, yv=0, trail=[], tail=5, score=0;
            document.onkeydown = e => { if(e.key=='ArrowLeft'&&xv!=1) {xv=-1;yv=0;} if(e.key=='ArrowUp'&&yv!=1) {xv=0;yv=-1;} if(e.key=='ArrowRight'&&xv!=-1) {xv=1;yv=0;} if(e.key=='ArrowDown'&&yv!=-1) {xv=0;yv=1;} };
            loopInt = setInterval(() => {
                px+=xv; py+=yv;
                if(px<0) px=tc-1; if(px>tc-1) px=0; if(py<0) py=tc-1; if(py>tc-1) py=0;
                ctx.fillStyle="black"; ctx.fillRect(0,0,400,400); ctx.fillStyle="lime";
                for(let i=0;i<trail.length;i++){ ctx.fillRect(trail[i].x*gs,trail[i].y*gs,gs-2,gs-2); if(trail[i].x==px && trail[i].y==py && tail>5) { tail=5; score=0; } }
                trail.push({x:px,y:py}); while(trail.length>tail) trail.shift();
                if(ax==px && ay==py) { tail++; score+=10; ax=Math.floor(Math.random()*tc); ay=Math.floor(Math.random()*tc); }
                ctx.fillStyle="red"; ctx.fillRect(ax*gs,ay*gs,gs-2,gs-2);
                ctx.fillStyle="white"; ctx.fillText("Score: "+score, 10, 20);
            }, 100);
        }

        // 2. JOGO DA VELHA
        function playTicTacToe(c) {
            document.getElementById('modal-title').innerText = "Jogo da Velha";
            c.innerHTML = '<div id="board" class="grid grid-cols-3 gap-2 w-64 bg-gray-800 p-2 rounded"></div><p id="msg" class="mt-4 text-xl font-bold dark:text-white"></p>';
            let board = ['','','','','','','','',''], turn = 'X', gameActive = true;
            let div = document.getElementById('board');
            const win = [[0,1,2],[3,4,5],[6,7,8],[0,3,6],[1,4,7],[2,5,8],[0,4,8],[2,4,6]];
            for(let i=0; i<9; i++) {
                let cell = document.createElement('div');
                cell.className = "h-20 bg-gray-200 flex items-center justify-center text-4xl font-bold cursor-pointer hover:bg-gray-300";
                cell.onclick = () => {
                    if(!gameActive || board[i]!=='') return;
                    board[i] = turn; cell.innerText = turn;
                    let won = win.some(w => board[w[0]]===turn && board[w[1]]===turn && board[w[2]]===turn);
                    if(won) { document.getElementById('msg').innerText = turn+" Venceu!"; gameActive=false; sendReward(50,10); }
                    else if(!board.includes('')) { document.getElementById('msg').innerText = "Empate!"; gameActive=false; }
                    else turn = turn==='X'?'O':'X';
                };
                div.appendChild(cell);
            }
        }

        // 3. FLAPPY BIRD
        function playFlappy(c) {
            document.getElementById('modal-title').innerText = "Flappy Bird";
            c.innerHTML = '<canvas id="fc" width="300" height="400" class="bg-blue-300"></canvas><p class="mt-2 text-gray-500">Clique para pular</p>';
            let ctx = document.getElementById('fc').getContext('2d');
            let by=200, g=1.5, j=-10, v=0, px=300, py=0, gap=120, score=0;
            c.onclick = () => v = j;
            loopInt = setInterval(() => {
                ctx.clearRect(0,0,300,400);
                v+=g; by+=v; px-=4;
                if(px<-50) { px=300; py=Math.floor(Math.random()*200)-100; score++; }
                ctx.fillStyle="green"; ctx.fillRect(px, 0, 50, 200+py); ctx.fillRect(px, 200+py+gap, 50, 400);
                ctx.fillStyle="yellow"; ctx.fillRect(50, by, 20, 20);
                ctx.fillStyle="black"; ctx.font="20px Arial"; ctx.fillText("Score: "+score, 10, 30);
                if(by>400 || by<0 || (50+20>px && 50<px+50 && (by<200+py || by+20>200+py+gap))) {
                    clearInterval(loopInt); alert("Game Over! Score: "+score); sendReward(score*5, score); closeGame();
                }
            }, 30);
        }

        // 4. JOKEMPO
        function playJokempo(c) {
            document.getElementById('modal-title').innerText = "Jokempo";
            c.innerHTML = `
                <div class="flex gap-4 text-4xl mb-6">
                    <button onclick="playJk('🪨')" class="p-4 bg-gray-200 rounded hover:bg-gray-300">🪨</button>
                    <button onclick="playJk('📄')" class="p-4 bg-gray-200 rounded hover:bg-gray-300">📄</button>
                    <button onclick="playJk('✂️')" class="p-4 bg-gray-200 rounded hover:bg-gray-300">✂️</button>
                </div>
                <div id="jk-res" class="text-2xl font-bold dark:text-white"></div>
            `;
            window.playJk = (p) => {
                let opts = ['🪨','📄','✂️'], pc = opts[Math.floor(Math.random()*3)];
                let res = "Empate!";
                if((p==='🪨'&&pc==='✂️')||(p==='📄'&&pc==='🪨')||(p==='✂️'&&pc==='📄')) { res="Você Venceu!"; sendReward(20,5); }
                else if(p!==pc) res="PC Venceu!";
                document.getElementById('jk-res').innerText = `Você: ${p} | PC: ${pc} -> ${res}`;
            };
        }

        // 5. SIMON (GENIUS)
        function playSimon(c) {
            document.getElementById('modal-title').innerText = "Genius";
            c.innerHTML = `<div class="grid grid-cols-2 gap-2 w-48 h-48 mb-4">
                <button id="s0" class="simon-btn bg-green-500 rounded-tl-full"></button><button id="s1" class="simon-btn bg-red-500 rounded-tr-full"></button>
                <button id="s2" class="simon-btn bg-yellow-400 rounded-bl-full"></button><button id="s3" class="simon-btn bg-blue-500 rounded-br-full"></button>
            </div><div id="s-st" class="font-bold dark:text-white">Nível 1</div>`;
            let seq = [], pSeq = [], lvl = 0, turn = false;
            function next() { pSeq=[]; lvl++; document.getElementById('s-st').innerText="Nível "+lvl+" - Observe"; seq.push(Math.floor(Math.random()*4)); playSeq(); }
            function playSeq() {
                turn=false; let i=0; loopInt = setInterval(()=>{
                    flash(seq[i]); i++; if(i>=seq.length){ clearInterval(loopInt); turn=true; document.getElementById('s-st').innerText="Sua vez!"; }
                }, 800);
            }
            function flash(id) { let b=document.getElementById('s'+id); b.classList.add('active'); setTimeout(()=>b.classList.remove('active'), 400); }
            for(let i=0;i<4;i++) document.getElementById('s'+i).onclick = () => {
                if(!turn) return; flash(i); pSeq.push(i);
                if(pSeq[pSeq.length-1] !== seq[pSeq.length-1]) { alert("Perdeu! Nível: "+lvl); sendReward(lvl*10, lvl); closeGame(); }
                else if(pSeq.length === seq.length) { turn=false; setTimeout(next, 1000); }
            };
            setTimeout(next, 500);
        }

        // 6. 2048
        function play2048(c) {
            document.getElementById('modal-title').innerText = "2048";
            c.innerHTML = '<canvas id="c2048" width="320" height="320" style="background:#bbada0;border-radius:10px;"></canvas><p id="sc2048" class="mt-2 dark:text-white font-bold"></p>';
            let ctx = document.getElementById('c2048').getContext('2d'), grid=[[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0]], sc=0;
            function add() { let e=[]; for(let r=0;r<4;r++)for(let c=0;c<4;c++)if(grid[r][c]===0)e.push({r,c}); if(e.length){let cl=e[Math.floor(Math.random()*e.length)]; grid[cl.r][cl.c]=Math.random()<0.9?2:4;} }
            function draw() {
                ctx.clearRect(0,0,320,320); document.getElementById('sc2048').innerText="Score: "+sc;
                let colors={0:"#cdc1b4",2:"#eee4da",4:"#ede0c8",8:"#f2b179",16:"#f59563",32:"#f67c5f",64:"#f65e3b",128:"#edcf72",256:"#edcc61",512:"#edc850",1024:"#edc53f",2048:"#edc22e"};
                for(let r=0;r<4;r++)for(let c=0;c<4;c++){
                    let v=grid[r][c]; ctx.fillStyle=colors[v]||"#3c3a32"; ctx.fillRect(c*80+5,r*80+5,70,70);
                    if(v>0){ ctx.fillStyle=v<=4?"#776e65":"white"; ctx.font="bold 24px Arial"; ctx.textAlign="center"; ctx.textBaseline="middle"; ctx.fillText(v,c*80+40,r*80+40); }
                }
            }
            function slide(row) { let a=row.filter(v=>v); for(let i=0;i<a.length-1;i++)if(a[i]===a[i+1]){a[i]*=2;sc+=a[i];a[i+1]=0;} a=a.filter(v=>v); while(a.length<4)a.push(0); return a; }
            document.onkeydown = e => {
                let og = JSON.stringify(grid);
                if(e.key==="ArrowLeft") for(let r=0;r<4;r++) grid[r]=slide(grid[r]);
                if(e.key==="ArrowRight") for(let r=0;r<4;r++) grid[r]=slide(grid[r].reverse()).reverse();
                if(e.key==="ArrowUp") for(let c=0;c<4;c++){ let col=[grid[0][c],grid[1][c],grid[2][c],grid[3][c]]; col=slide(col); for(let r=0;r<4;r++)grid[r][c]=col[r]; }
                if(e.key==="ArrowDown") for(let c=0;c<4;c++){ let col=[grid[0][c],grid[1][c],grid[2][c],grid[3][c]]; col=slide(col.reverse()).reverse(); for(let r=0;r<4;r++)grid[r][c]=col[r]; }
                if(["ArrowUp","ArrowDown","ArrowLeft","ArrowRight"].includes(e.key)){ e.preventDefault(); if(og!==JSON.stringify(grid)){add();draw();} }
            };
            add();add();draw();
        }

        // 7. BREAKOUT
        function playBreakout(c) {
            document.getElementById('modal-title').innerText = "Breakout";
            c.innerHTML = '<canvas id="brkc" width="480" height="320"></canvas>';
            let ctx = document.getElementById('brkc').getContext('2d'), x=240, y=300, dx=3, dy=-3, pd=200, score=0;
            let bricks=[], r=4, cl=6; for(let i=0;i<cl;i++){ bricks[i]=[]; for(let j=0;j<r;j++)bricks[i][j]={x:0,y:0,st:1}; }
            document.onkeydown = e => { if(e.key==="ArrowLeft" && pd>0)pd-=20; if(e.key==="ArrowRight" && pd<405)pd+=20; };
            function draw() {
                ctx.clearRect(0,0,480,320); ctx.beginPath(); ctx.arc(x,y,8,0,Math.PI*2); ctx.fillStyle="#fff"; ctx.fill(); ctx.closePath();
                ctx.fillStyle="#38bdf8"; ctx.fillRect(pd,310,75,10); ctx.fillStyle="white"; ctx.fillText("Score: "+score,8,20);
                for(let i=0;i<cl;i++)for(let j=0;j<r;j++){
                    if(bricks[i][j].st){
                        let bx=i*75+20, by=j*30+30; bricks[i][j].x=bx; bricks[i][j].y=by;
                        ctx.fillStyle="#f43f5e"; ctx.fillRect(bx,by,65,20);
                        if(x>bx && x<bx+65 && y>by && y<by+20){ dy=-dy; bricks[i][j].st=0; score+=10; }
                    }
                }
                if(x+dx>472||x+dx<8) dx=-dx; if(y+dy<8) dy=-dy;
                else if(y+dy>302){ if(x>pd&&x<pd+75){dy=-dy;} else{alert("Game Over! Score: "+score);sendReward(score,Math.floor(score/5));closeGame();return;} }
                x+=dx; y+=dy; animReq = requestAnimationFrame(draw);
            }
            draw();
        }

        // 8. SPACE INVADERS
        function playInvaders(c) {
            document.getElementById('modal-title').innerText = "Invaders";
            c.innerHTML = '<canvas id="invc" width="400" height="400" class="bg-black"></canvas>';
            let ctx = document.getElementById('invc').getContext('2d'), px=180, bul=[], en=[], sc=0, dir=1;
            for(let r=0;r<4;r++)for(let cl=0;cl<7;cl++)en.push({x:cl*45+30,y:r*35+30,st:1});
            document.onkeydown=e=>{ if(e.key==='ArrowLeft')px-=15; if(e.key==='ArrowRight')px+=15; if(e.key===' ')bul.push({x:px+18,y:370}); };
            function draw(){
                ctx.clearRect(0,0,400,400); ctx.fillStyle='#0f0'; ctx.fillRect(px,370,40,20);
                ctx.fillStyle='#ff0'; for(let i=bul.length-1;i>=0;i--){ bul[i].y-=7; ctx.fillRect(bul[i].x,bul[i].y,4,10); if(bul[i].y<0)bul.splice(i,1); }
                let edge=false, all=true; ctx.fillStyle='#f00';
                en.forEach(e=>{ if(e.st){ all=false; e.x+=dir; if(e.x>370||e.x<0)edge=true; ctx.fillRect(e.x,e.y,30,20);
                    for(let i=bul.length-1;i>=0;i--){ let b=bul[i]; if(b.x<e.x+30&&b.x>e.x&&b.y<e.y+20&&b.y>e.y){e.st=0;bul.splice(i,1);sc+=20;} }
                }});
                if(edge){dir*=-1;en.forEach(e=>{if(e.st)e.y+=20;});}
                ctx.fillStyle="white"; ctx.fillText("Score: "+sc,10,20);
                if(all){alert("Vitória!");sendReward(sc,30);closeGame();return;}
                animReq=requestAnimationFrame(draw);
            }
            draw();
        }

        // 9. MINAS
        function playMines(c) {
            document.getElementById('modal-title').innerText = "Minas";
            c.innerHTML = '<div id="mgrid" class="grid grid-cols-8 gap-0 bg-gray-400 p-1 rounded"></div>';
            let bd=[], g=document.getElementById('mgrid'), mc=10;
            for(let r=0;r<8;r++){bd[r]=[];for(let cl=0;cl<8;cl++)bd[r][cl]={m:false,r:false,c:0,el:null};}
            let m=0; while(m<mc){let r=Math.floor(Math.random()*8),cl=Math.floor(Math.random()*8); if(!bd[r][cl].m){bd[r][cl].m=true;m++;}}
            for(let r=0;r<8;r++)for(let cl=0;cl<8;cl++)if(!bd[r][cl].m)for(let i=-1;i<=1;i++)for(let j=-1;j<=1;j++)if(r+i>=0&&r+i<8&&cl+j>=0&&cl+j<8&&bd[r+i][cl+j].m)bd[r][cl].c++;
            function rev(r,cl){
                if(r<0||r>=8||cl<0||cl>=8||bd[r][cl].r)return; bd[r][cl].r=true; let el=bd[r][cl].el; el.classList.add('revealed');
                if(bd[r][cl].m){el.innerText='💣';el.style.background='red';setTimeout(()=>alert("BOOM!"),100);return;}
                if(bd[r][cl].c>0) el.innerText=bd[r][cl].c; else for(let i=-1;i<=1;i++)for(let j=-1;j<=1;j++)rev(r+i,cl+j);
            }
            for(let r=0;r<8;r++)for(let cl=0;cl<8;cl++){
                let d=document.createElement('div'); d.className='mine-cell'; d.onclick=()=>rev(r,cl);
                bd[r][cl].el=d; g.appendChild(d);
            }
        }

        // 10. FORCA
        function playHangman(c) {
            document.getElementById('modal-title').innerText = "Forca";
            c.innerHTML = '<div id="hw" class="text-3xl font-mono mb-4 dark:text-white tracking-widest"></div><div id="hk" class="grid grid-cols-7 gap-2"></div>';
            let w="LARAVEL", g=new Set(), err=0, dw=document.getElementById('hw');
            function up(){ let d=w.split('').map(l=>g.has(l)?l:"_").join(" "); dw.innerText=d; if(!d.includes("_"))alert("Venceu!"); else if(err>=6)alert("Perdeu! Era "+w); }
            let kb=document.getElementById('hk'); "ABCDEFGHIJKLMNOPQRSTUVWXYZ".split('').forEach(l=>{
                let b=document.createElement('button'); b.className="p-2 bg-indigo-500 text-white rounded disabled:opacity-50"; b.innerText=l;
                b.onclick=()=>{ b.disabled=true; g.add(l); if(!w.includes(l))err++; up(); }; kb.appendChild(b);
            }); up();
        }

        // 11. PACMAN
        function playPacman(c) {
            document.getElementById('modal-title').innerText = "Pac-Man";
            c.innerHTML = '<canvas id="pc" width="300" height="300" class="bg-black"></canvas><p id="ps" class="text-white mt-2 font-bold"></p>';
            let ctx=document.getElementById('pc').getContext('2d'), px=150, py=150, pdx=0, pdy=0, ts=20, sc=0;
            let pils=[]; for(let i=1;i<14;i+=2)for(let j=1;j<14;j+=2)pils.push({x:i*ts+ts/2,y:j*ts+ts/2,st:1});
            document.onkeydown=e=>{if(e.key=='ArrowLeft'){pdx=-2;pdy=0;}if(e.key=='ArrowRight'){pdx=2;pdy=0;}if(e.key=='ArrowUp'){pdx=0;pdy=-2;}if(e.key=='ArrowDown'){pdx=0;pdy=2;}};
            loopInt=setInterval(()=>{
                ctx.clearRect(0,0,300,300); px+=pdx; py+=pdy; if(px<0)px=300; if(px>300)px=0; if(py<0)py=300; if(py>300)py=0;
                ctx.fillStyle="yellow"; ctx.beginPath(); ctx.arc(px,py,8,0,Math.PI*2); ctx.fill();
                ctx.fillStyle="white"; pils.forEach(p=>{ if(p.st){ ctx.beginPath(); ctx.arc(p.x,p.y,3,0,Math.PI*2); ctx.fill();
                    if(Math.abs(px-p.x)<10 && Math.abs(py-p.y)<10){p.st=0;sc+=10;} }});
                document.getElementById('ps').innerText="Score: "+sc;
            }, 30);
        }

        // 12. MEMÓRIA
        function playMemory(c) {
            document.getElementById('modal-title').innerText = "Memória";
            c.innerHTML = '<div id="mg" class="grid grid-cols-4 gap-4 bg-gray-700 p-4 rounded-xl"></div>';
            let e=['🍎','🍌','🍉','🍇','🍓','🥑','🥕','🌽'], cd=[...e,...e].sort(()=>0.5-Math.random()), g=document.getElementById('mg');
            let f, s, lk=false, m=0;
            function fl(){ if(lk||this===f)return; this.classList.add('flip'); if(!f){f=this;return;} s=this;
                if(f.dataset.e===s.dataset.e){m++;f.onclick=null;s.onclick=null; [f,s,lk]=[null,null,false]; if(m===8)alert("Venceu!"); }
                else{ lk=true; setTimeout(()=>{f.classList.remove('flip');s.classList.remove('flip');[f,s,lk]=[null,null,false];},800); }
            }
            cd.forEach(em=>{ let d=document.createElement('div'); d.className='memory-card'; d.dataset.e=em; d.innerHTML=`<div class="front">❓</div><div class="back">${em}</div>`; d.onclick=fl; g.appendChild(d); });
        }
    </script>
</body>
</html>