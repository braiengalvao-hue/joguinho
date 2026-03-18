@extends('layouts.arcade')

@section('title', 'Menu Principal - Arcade Portal')

@section('content')
<style>
    .game-card:hover { transform: scale(1.05); transition: all 0.2s; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.3); }
</style>

<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 w-full">
    
    <a href="{{ route('arcade.snake') }}" class="game-card bg-green-500 rounded-xl h-24 flex items-center justify-center text-white font-bold text-lg shadow">🐍 Snake</a>
    <a href="{{ route('arcade.flappy') }}" class="game-card bg-sky-500 rounded-xl h-24 flex items-center justify-center text-white font-bold text-lg shadow">🐦 Flappy</a>
    
    <a href="{{ route('arcade.pacman') }}" class="game-card bg-yellow-400 rounded-xl h-24 flex items-center justify-center text-black font-bold text-lg shadow">🟡 Pacman</a>
    
    <a href="{{ route('arcade.sudoku') }}" class="game-card bg-purple-500 rounded-xl h-24 flex items-center justify-center text-white font-bold text-lg shadow">🔢 Sudoku</a>

    <a href="{{ route('arcade.velha') }}" class="game-card bg-blue-500 rounded-xl h-24 flex items-center justify-center text-white font-bold text-lg shadow">❌ Velha</a>
    
    <a href="{{ route('arcade.minas') }}" class="game-card bg-gray-500 rounded-xl h-24 flex items-center justify-center text-white font-bold text-lg shadow">💣 Minas</a>
    
    <a href="{{ route('arcade.clash') }}" class="game-card bg-blue-700 rounded-xl h-24 flex items-center justify-center text-white font-bold text-xl shadow border-2 border-fuchsia-500 overflow-hidden relative">
        <span class="z-10">👑 Clash</span>
        <div class="absolute bottom-0 w-full h-1/2 bg-red-600 opacity-50 skew-y-6 transform translate-y-2"></div>
    </a>
    <a href="{{ route('arcade.rambo') }}" class="game-card bg-red-800 rounded-xl h-24 flex items-center justify-center text-white font-black text-xl shadow border-2 border-red-400 overflow-hidden relative">
        <span class="z-10 italic drop-shadow-md">💥 COMANDO</span>
        <div class="absolute inset-0 opacity-20 bg-[repeating-linear-gradient(45deg,transparent,transparent_10px,#000_10px,#000_20px)]"></div>
    </a>

    <a href="{{ route('arcade.freefire') }}" class="game-card bg-orange-600 rounded-xl h-24 flex items-center justify-center text-white font-bold text-xl tracking-widest italic shadow border-2 border-yellow-400">🔥 FREE FIRE</a>
</div>
@endsection