@extends('layouts.arcade')
@section('title', 'Campo Minado - Arcade')

@section('content')
<style>
    .mine-cell { width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem; cursor: pointer; background-color: #cbd5e1; border: 3px outset #f8fafc; }
    .mine-cell.revealed { background-color: #e2e8f0; border: 1px solid #94a3b8; }
</style>

<div class="bg-gray-300 dark:bg-gray-700 p-6 rounded-xl shadow-2xl relative w-full max-w-md text-center border-4 border-gray-500">
    <a href="{{ route('arcade.index') }}" class="absolute -top-4 -right-4 bg-red-600 text-white w-10 h-10 rounded-full font-bold text-xl border-2 border-white hover:bg-red-700 flex items-center justify-center decoration-transparent">&times;</a>
    
    <div class="flex justify-between items-center mb-4 bg-gray-800 text-white p-2 rounded">
        <span class="text-xl font-bold text-red-400">💣 <span id="bomb-count">10</span></span>
        <button onclick="iniciarMinas()" class="text-2xl hover:scale-110 transition">🔄</button>
    </div>
    
    <div id="mgrid" class="grid grid-cols-8 gap-0 bg-gray-400 p-1 border-4 border-gray-600 rounded mx-auto w-fit"></div>
    <p class="mt-4 text-sm font-bold text-gray-700 dark:text-gray-300">Botão Esquerdo: Revelar | Botão Direito: Bandeira 🚩</p>
</div>
@endsection

@push('scripts')
<script>
    let bd = [], mc = 10, rows = 8, cols = 8;
    let gameActive = true;

    function iniciarMinas() {
        gameActive = true;
        let g = document.getElementById('mgrid');
        g.innerHTML = '';
        bd = [];
        
        for(let r=0; r<rows; r++){
            bd[r] = [];
            for(let c=0; c<cols; c++) bd[r][c] = { m: false, r: false, count: 0, el: null, flag: false };
        }
        
        // Plantar minas
        let m = 0; 
        while(m < mc){
            let r = Math.floor(Math.random()*rows), c = Math.floor(Math.random()*cols); 
            if(!bd[r][c].m){ bd[r][c].m = true; m++; }
        }
        
        // Calcular números
        for(let r=0; r<rows; r++) {
            for(let c=0; c<cols; c++) {
                if(!bd[r][c].m) {
                    for(let i=-1; i<=1; i++) {
                        for(let j=-1; j<=1; j++) {
                            if(r+i>=0 && r+i<rows && c+j>=0 && c+j<cols && bd[r+i][c+j].m) bd[r][c].count++;
                        }
                    }
                }
            }
        }
        
        // Desenhar
        for(let r=0; r<rows; r++) {
            for(let c=0; c<cols; c++) {
                let d = document.createElement('div'); 
                d.className = 'mine-cell'; 
                
                d.onclick = () => revelar(r, c);
                d.oncontextmenu = (e) => {
                    e.preventDefault();
                    if(!gameActive || bd[r][c].r) return;
                    bd[r][c].flag = !bd[r][c].flag;
                    d.innerText = bd[r][c].flag ? '🚩' : '';
                };
                
                bd[r][c].el = d; 
                g.appendChild(d);
            }
        }
    }

    function revelar(r, c) {
        if(!gameActive || r<0 || r>=rows || c<0 || c>=cols || bd[r][c].r || bd[r][c].flag) return;
        
        bd[r][c].r = true; 
        let el = bd[r][c].el; 
        el.classList.add('revealed');
        
        if(bd[r][c].m) {
            el.innerText = '💣'; el.style.background = 'red';
            gameActive = false;
            setTimeout(() => alert("KABOOM! Você perdeu."), 100);
            return;
        }
        
        if(bd[r][c].count > 0) {
            el.innerText = bd[r][c].count;
            const colors = ['','blue','green','red','purple','maroon','turquoise','black','gray'];
            el.style.color = colors[bd[r][c].count];
        } else {
            for(let i=-1; i<=1; i++) for(let j=-1; j<=1; j++) revelar(r+i, c+j);
        }
    }

    window.onload = iniciarMinas;
</script>
@endpush