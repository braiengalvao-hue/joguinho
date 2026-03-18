@extends('layouts.arcade')
@section('title', 'Jogo da Velha - Arcade')

@section('content')
<div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-2xl relative w-full max-w-sm text-center border-4 border-blue-500">
    <a href="{{ route('arcade.index') }}" class="absolute -top-4 -right-4 bg-red-600 text-white w-10 h-10 rounded-full font-bold text-xl border-2 border-white hover:bg-red-700 flex items-center justify-center decoration-transparent">&times;</a>
    
    <h2 class="text-3xl font-black mb-4 dark:text-white uppercase text-blue-600">Jogo da Velha</h2>
    
    <div id="board" class="grid grid-cols-3 gap-2 bg-gray-800 p-2 rounded-lg mx-auto w-64 h-64"></div>
    
    <p id="msg" class="mt-4 text-2xl font-bold dark:text-white text-gray-800 h-8"></p>
    <button onclick="iniciarVelha()" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Reiniciar</button>
</div>
@endsection

@push('scripts')
<script>
    let board, turn, gameActive;
    const winConditions = [[0,1,2],[3,4,5],[6,7,8],[0,3,6],[1,4,7],[2,5,8],[0,4,8],[2,4,6]];

    function iniciarVelha() {
        board = ['', '', '', '', '', '', '', '', ''];
        turn = 'X';
        gameActive = true;
        document.getElementById('msg').innerText = "Vez do: " + turn;
        
        const divBoard = document.getElementById('board');
        divBoard.innerHTML = ''; // Limpa o tabuleiro
        
        for(let i=0; i<9; i++) {
            let cell = document.createElement('div');
            cell.className = "bg-gray-200 flex items-center justify-center text-5xl font-bold cursor-pointer hover:bg-gray-300 rounded";
            cell.onclick = () => jogarVelha(i, cell);
            divBoard.appendChild(cell);
        }
    }

    function jogarVelha(i, cell) {
        if(!gameActive || board[i] !== '') return;
        
        board[i] = turn;
        cell.innerText = turn;
        cell.classList.add(turn === 'X' ? 'text-blue-600' : 'text-red-600');
        
        let won = winConditions.some(w => board[w[0]] === turn && board[w[1]] === turn && board[w[2]] === turn);
        
        if(won) { 
            document.getElementById('msg').innerText = "🎉 " + turn + " Venceu!"; 
            gameActive = false; 
        }
        else if(!board.includes('')) { 
            document.getElementById('msg').innerText = "🤝 Deu Velha (Empate)!"; 
            gameActive = false; 
        }
        else {
            turn = turn === 'X' ? 'O' : 'X';
            document.getElementById('msg').innerText = "Vez do: " + turn;
        }
    }

    window.onload = iniciarVelha;
</script>
@endpush