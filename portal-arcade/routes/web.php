<?php

use Illuminate\Support\Facades\Route;

// Agrupando as rotas do arcade para ficar organizado
Route::prefix('arcade')->group(function () {
    Route::get('/', function () { return view('arcade.index'); })->name('arcade.index');
    Route::get('/snake', function () { return view('arcade.snake'); })->name('arcade.snake');
    Route::get('/velha', function () { return view('arcade.velha'); })->name('arcade.velha');
    // Novas rotas abaixo:
    Route::get('/flappy', function () { return view('arcade.flappy'); })->name('arcade.flappy');
    Route::get('/pacman', function () { return view('arcade.pacman'); })->name('arcade.pacman');
    Route::get('/sudoku', function () { return view('arcade.sudoku'); })->name('arcade.sudoku');
    Route::get('/velha', function () { return view('arcade.velha'); })->name('arcade.velha');
    Route::get('/minas', function () { return view('arcade.minas'); })->name('arcade.minas');
    Route::get('/freefire', function () { return view('arcade.freefire'); })->name('arcade.freefire');
    // Coloque logo abaixo da rota do freefire
    Route::get('/clash', function () { return view('arcade.clash'); })->name('arcade.clash');   
    Route::get('/rambo', function () { return view('arcade.rambo'); })->name('arcade.rambo');
});