<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'WhatsApp SaaS' }}</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    @livewireStyles
    @livewireScripts
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Simple Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fab fa-whatsapp text-green-500 text-2xl"></i>
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900">WhatsApp SaaS</h1>
                        <p class="text-sm text-gray-500">Message Management</p>
                    </div>
                </div>

                <div class="flex spams-center space-x-6">
                    <nav class="flex space-x-6">
                        <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('dashboard') ? 'nav-active' : '' }}">
                            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                        </a>
                        <a href="{{ route('api-keys') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('api-keys') ? 'nav-active' : '' }}">
                            <i class="fas fa-key mr-2"></i>API Keys
                        </a>
                    </nav>

                    <!-- User Menu -->
                    <div class="flex items-center space-x-3 border-l border-gray-200 pl-6">
                        @auth
                        <span class="text-sm text-gray-600">{{ Auth::user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-700 hover:text-red-600 px-3 py-2 rounded-md text-sm font-medium">
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

    <!-- Socket.IO -->
    <script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>
    @stack('scripts')
</body>

</html>