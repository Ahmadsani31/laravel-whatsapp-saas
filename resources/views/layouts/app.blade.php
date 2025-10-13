<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="whatsapp-engine-url" content="{{ env('WHATSAPP_ENGINE_URL', 'http://localhost:3000') }}">
    <meta name="app-debug" content="{{ config('app.debug') ? 'true' : 'false' }}">
    <meta name="theme-preference" content="{{ session('theme', 'light') }}">
    <title>{{ $title ?? 'WhatsApp SaaS' }}</title>

    <!-- Dark Mode Script (Flowbite approach) -->
    <script>
        // On page load or when changing themes, best to add inline in `head` to avoid FOUC
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Notyf CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3.10.0/notyf.min.css">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    @livewireStyles
    @livewireScripts
</head>

<body class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <!-- Simple Header -->
    <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fab fa-whatsapp text-green-500 text-2xl"></i>
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900 dark:text-white">WhatsApp SaaS</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Message Management</p>
                    </div>
                </div>

                <div class="flex items-center space-x-6">
                    <nav class="flex space-x-6">
                        
                        <a href="{{ route('dashboard') }}" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('dashboard') ? 'nav-active' : '' }}">
                            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                        </a>
                        <a href="{{ route('campaigns') }}" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('campaigns*') ? 'nav-active' : '' }}">
                            <i class="fas fa-bullhorn mr-2"></i>Campaigns
                        </a>
                        <a href="{{ route('api-keys') }}" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('api-keys') ? 'nav-active' : '' }}">
                            <i class="fas fa-key mr-2"></i>API Keys
                        </a>

                    </nav>

                    <!-- Theme Toggle & User Menu -->
                    <div class="flex items-center space-x-3 border-l border-gray-200 dark:border-gray-700 pl-6">
                        <!-- Theme Toggle -->
                        <button id="theme-toggle" type="button" class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2.5" title="Toggle Dark Mode">
                            <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                            </svg>
                            <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                            </svg>
                        </button>

                        @auth
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ Auth::user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </button>
                        </form>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-6">
        @if(isset($slot))
        {{ $slot }}
        @else
        @yield('content')
        @endif
    </main>

    <!-- Notyf JS -->
    <script src="https://cdn.jsdelivr.net/npm/notyf@3.10.0/notyf.min.js"></script>
    
    <!-- Socket.IO -->
    <script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>
    
    @stack('scripts')
</body>

</html>