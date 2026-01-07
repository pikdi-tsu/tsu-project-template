<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - TSU Homebase</title>

    <link rel="icon" href="{{ asset('public/assetsku/img/favicon/favicon.ico') }}" type="image/x-icon"/>
    <link rel="shortcut icon" href="{{ asset('public/assetsku/img/favicon/favicon.ico') }}" type="image/x-icon"/>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
    <script>
        // Init Theme
        let theme = localStorage.getItem('theme') || 'system';
        if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 transition-colors duration-300">

<div class="absolute top-4 right-4">
    <button id="theme-toggle" type="button" class="p-2 rounded-full text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none transition-colors">
        <svg id="icon-sun" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
        <svg id="icon-moon" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
        <svg id="icon-system" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
    </button>
</div>

<div class="min-h-screen flex flex-col items-center justify-center text-center px-4">
    <div class="mb-6">
        <a href="{{ route('dashboard') }}" class="flex justify-center transform hover:scale-105 transition-transform duration-300">
            <img src="{{ asset('public/assetsku/img/logotsu.png') }}" alt="Ikon Tiga Serangkai University" width="60px" />
        </a>
    </div>

    {{-- JUDUL: Bisa dari controller ($title) atau dari section anak ('title') --}}
    <h1 class="text-4xl font-bold text-gray-800 dark:text-gray-200">
        {{ $title ?? $__env->yieldContent('title') }}
    </h1>

    {{-- KODE ERROR  --}}
    <div class="text-gray-400 font-mono text-5xl mt-2">
        @yield('code')
    </div>

    {{-- PESAN --}}
    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400 max-w-5xl mx-auto">
        {{-- Cek variabel $message (Custom dari Controller) --}}
        {{-- Cek $exception message (Bawaan Laravel) --}}
        {{-- Cek Section 'message' (Default statis dari file anak) --}}
        {{ $message ?? ($exception->getMessage() ?: $__env->yieldContent('message')) }}
    </p>

    <p class="mt-8 text-gray-500 dark:text-gray-300">
        Kembali ke Halaman Sebelumnya atau
        <a href="{{ route('indexing') }}" class="text-blue-500 dark:text-blue-400 underline hover:text-blue-700 dark:hover:text-blue-300 transition-colors">Dashboard TSU Template</a>
    </p>
</div>

<script>
    const toggleBtn = document.getElementById('theme-toggle');
    const sunIcon = document.getElementById('icon-sun');
    const moonIcon = document.getElementById('icon-moon');
    const systemIcon = document.getElementById('icon-system');
    const cycle = ['system', 'light', 'dark'];

    function updateThemeAndIcons() {
        let theme = localStorage.getItem('theme') || 'system';
        if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        sunIcon.style.display = theme === 'light' ? 'block' : 'none';
        moonIcon.style.display = theme === 'dark' ? 'block' : 'none';
        systemIcon.style.display = theme === 'system' ? 'block' : 'none';
    }

    toggleBtn.addEventListener('click', () => {
        let currentTheme = localStorage.getItem('theme') || 'system';
        let currentIndex = cycle.indexOf(currentTheme);
        let nextIndex = (currentIndex + 1) % cycle.length;
        let newTheme = cycle[nextIndex];
        localStorage.setItem('theme', newTheme);
        updateThemeAndIcons();
    });
    updateThemeAndIcons();
</script>
</body>
</html>
