@extends('layouts.arcade')
@section('title', 'Sudoku - Arcade')

@section('content')
<style>
    .sudoku-grid { display: grid; grid-template-columns: repeat(9, 1fr); gap: 1px; background: #333; border: 3px solid #111; max-width: 400px; margin: 0 auto; }
    .sudoku-cell { width: 40px; height: 40px; text-align: center; font-size: 20px; font-weight: bold; border: none; outline: none; background: white; }
    /* Bordas grossas para dividir os blocos de 3x3 */
    .sudoku-cell:nth-child(3n) { border-right: 3px solid #111; }
    .sudoku-cell:nth-child(n+19):nth-child(-n+27), .sudoku-cell:nth-child(n+46):nth-child(-n+54) { border-bottom: 3px solid #111; }
    .sudoku-cell[readonly] { background: #e2e8f0; color: #333; }
    .sudoku-cell:focus { background: #bfdbfe; }
</style>

<div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-2xl relative w-full max-w-2xl text-center border-4 border-gray-400">
    <a href="{{ route('arcade.index') }}" class="absolute -top-4 -right-4 bg-red-600 text-white w-10 h-10 rounded-full font-bold text-xl border-2 border-white hover:bg-red-700 flex items-center justify-center decoration-transparent">&times;</a>
    <h2 class="text-3xl font-black mb-4 text-gray-800 dark:text-gray-200 uppercase">Sudoku</h2>
    
    <div id="sudoku-board" class="sudoku-grid"></div>

    <button onclick="checkSudoku()" class="mt-6 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-6 rounded-lg text-lg">Verificar Solução</button>
</div>
@endsection

@push('scripts')
<script>
    // 0 = Célula vazia (para o jogador preencher)
    const puzzle = [
        [5,3,0,0,7,0,0,0,0], [6,0,0,1,9,5,0,0,0], [0,9,8,0,0,0,0,6,0],
        [8,0,0,0,6,0,0,0,3], [4,0,0,8,0,3,0,0,1], [7,0,0,0,2,0,0,0,6],
        [0,6,0,0,0,0,2,8,0], [0,0,0,4,1,9,0,0,5], [0,0,0,0,8,0,0,7,9]
    ];
    
    const solution = [
        [5,3,4,6,7,8,9,1,2], [6,7,2,1,9,5,3,4,8], [1,9,8,3,4,2,5,6,7],
        [8,5,9,7,6,1,4,2,3], [4,2,6,8,5,3,7,9,1], [7,1,3,9,2,4,8,5,6],
        [9,6,1,5,3,7,2,8,4], [2,8,7,4,1,9,6,3,5], [3,4,5,2,8,6,1,7,9]
    ];

    const boardDiv = document.getElementById('sudoku-board');

    // Gerar a tabela
    for (let r = 0; r < 9; r++) {
        for (let c = 0; c < 9; c++) {
            let input = document.createElement('input');
            input.type = 'text';
            input.maxLength = 1;
            input.className = 'sudoku-cell';
            input.dataset.row = r;
            input.dataset.col = c;
            
            if (puzzle[r][c] !== 0) {
                input.value = puzzle[r][c];
                input.readOnly = true;
            } else {
                // Restringir entrada apenas para números de 1 a 9
                input.addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^1-9]/g, '');
                });
            }
            boardDiv.appendChild(input);
        }
    }

    function checkSudoku() {
        let inputs = document.querySelectorAll('.sudoku-cell');
        let isCorrect = true;
        let isComplete = true;

        inputs.forEach(input => {
            let r = input.dataset.row;
            let c = input.dataset.col;
            let val = parseInt(input.value);

            if (!val) { isComplete = false; }
            else if (val !== solution[r][c]) {
                isCorrect = false;
                input.style.color = 'red'; // Marca o erro
            } else {
                input.style.color = input.readOnly ? '#333' : 'blue'; // Marca o acerto
            }
        });

        if (!isComplete) { alert("Preencha todas as casas!"); }
        else if (isCorrect) { alert("Parabéns! Você resolveu o Sudoku!"); }
        else { alert("Existem erros. As marcações em vermelho estão incorretas."); }
    }
</script>
@endpush