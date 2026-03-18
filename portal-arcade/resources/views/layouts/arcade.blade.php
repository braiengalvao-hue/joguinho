<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Arcade Portal')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <style>
        /* Estilos globais úteis */
        canvas { display: block; margin: 0 auto; background: #111; border-radius: 4px; border: 2px solid #333; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-100 dark:bg-gray-900 transition-colors duration-300 min-h-screen flex flex-col">
    <div class="flex justify-between items-center p-4 bg-indigo-600 text-white shadow-lg">
        <a href="{{ route('arcade.index') }}" class="text-3xl font-bold tracking-wider hover:text-gray-200 transition">🎮 ARCADE PORTAL</a>
        <div class="flex items-center gap-6">
            <div class="bg-indigo-800 px-4 py-2 rounded-lg font-mono text-lg shadow-inner">💰 <span id="moedas-display" class="text-yellow-400 font-bold">0</span> | 🌟 <span id="xp-display" class="text-blue-300 font-bold">0</span></div>
            <button onclick="document.documentElement.classList.toggle('dark')" class="bg-gray-800 p-2 rounded-full hover:bg-gray-700">🌙/☀️</button>
        </div>
    </div>

    <main class="flex-grow container mx-auto p-8 flex flex-col items-center">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>