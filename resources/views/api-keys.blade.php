<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>API Keys Management - WhatsApp SaaS</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @livewireStyles

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .gradient-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            z-index: -1;
        }

        .card-shadow {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .header-btn {
            transition: all 0.3s ease;
            position: relative;
        }

        .header-btn:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .header-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        .header-btn:active:not(:disabled) {
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            .header-title {
                font-size: 1.5rem;
            }
            .header-subtitle {
                font-size: 0.75rem;
            }
        }

        /* Enhanced header layout */
        .header-info-badge {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .header-divider {
            background: linear-gradient(to bottom, transparent, rgba(255, 255, 255, 0.3), transparent);
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="gradient-bg text-white shadow-lg">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <!-- Left Side - Logo and Title -->
                <div class="flex items-center space-x-4">
                    <i class="fas fa-key text-3xl"></i>
                    <div>
                        <h1 class="text-2xl font-bold header-title">API Keys Management</h1>
                        <p class="text-sm opacity-75 header-subtitle">Manage API Keys for AI Agents</p>
                    </div>
                </div>
                
                <!-- Right Side - User Info and Controls -->
                <div class="flex items-center space-x-4">
                    <!-- Time Display -->
                    <div class="text-sm opacity-90 hidden lg:flex items-center header-info-badge px-3 py-1 rounded-full">
                        <i class="fas fa-clock mr-2"></i>
                        <span id="current-time"></span>
                    </div>
                    
                    <!-- User Info -->
                    <div class="hidden md:flex items-center header-info-badge px-3 py-1 rounded-full">
                        <i class="fas fa-user mr-2 text-sm"></i>
                        <span class="text-sm font-medium">{{ Auth::user()->name }}</span>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex items-center space-x-1">
                        <a href="{{ route('dashboard') }}" class="header-btn p-2 hover:bg-white hover:bg-opacity-20 rounded-full transition-colors" title="Back to Dashboard">
                            <i class="fas fa-arrow-left text-lg"></i>
                        </a>
                        <div class="w-px h-6 header-divider mx-2"></div>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="header-btn p-2 hover:bg-red-500 hover:bg-opacity-20 rounded-full transition-colors" title="Logout">
                                <i class="fas fa-sign-out-alt text-lg"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-6 py-8">
        @livewire('api-key-manager')
    </main>

    @livewireScripts

    <script>
        // Update current time
        function updateTime() {
            const now = new Date();
            const timeElement = document.getElementById('current-time');
            if (timeElement) {
                timeElement.textContent = now.toLocaleString();
            }
        }

        // Initialize time update
        updateTime();
        setInterval(updateTime, 1000);
    </script>
</body>
</html>