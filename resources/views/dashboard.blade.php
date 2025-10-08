<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WhatsApp SaaS - Dashboard</title>

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

    <!-- Socket.IO -->
    <script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @livewireStyles

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .card-shadow {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }

        .status-connected {
            background-color: #10b981;
        }

        .status-qr {
            background-color: #f59e0b;
        }

        .status-disconnected {
            background-color: #ef4444;
        }

        /* Header improvements */
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

        /* Header responsive */
        @media (max-width: 768px) {
            .header-title {
                font-size: 1.5rem;
            }

            .header-subtitle {
                font-size: 0.75rem;
            }
        }

        /* Button hover effects */
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

        /* Status indicator animation */
        .status-indicator {
            animation: statusPulse 2s infinite;
            box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4);
        }

        .status-connected {
            animation: statusPulseGreen 2s infinite;
        }

        .status-qr {
            animation: statusPulseYellow 2s infinite;
        }

        .status-disconnected {
            animation: statusPulseRed 2s infinite;
        }

        @keyframes statusPulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        @keyframes statusPulseGreen {
            0% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4);
            }

            70% {
                box-shadow: 0 0 0 4px rgba(16, 185, 129, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        @keyframes statusPulseYellow {
            0% {
                box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4);
            }

            70% {
                box-shadow: 0 0 0 4px rgba(245, 158, 11, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(245, 158, 11, 0);
            }
        }

        @keyframes statusPulseRed {
            0% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
            }

            70% {
                box-shadow: 0 0 0 4px rgba(239, 68, 68, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
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

        /* Improved feature cards */
        .feature-card {
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-4px);
        }

        .feature-icon-bg {
            transition: all 0.3s ease;
        }

        .feature-card:hover .feature-icon-bg {
            transform: scale(1.1);
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
                    <i class="fab fa-whatsapp text-3xl"></i>
                    <div>
                        <h1 class="text-2xl font-bold header-title">WhatsApp SaaS</h1>
                        <p class="text-sm opacity-75 header-subtitle">Professional WhatsApp Management</p>
                    </div>
                </div>

                <!-- Center - Connection Status -->
                <div class="flex-1 flex justify-center">
                    <div id="connection-status" class="flex items-center text-sm bg-white bg-opacity-20 px-4 py-2 rounded-full">
                        <span class="status-indicator status-disconnected mr-2"></span>
                        <span class="font-medium">Disconnected</span>
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
                        <button id="refresh-all" class="header-btn p-2 hover:bg-white hover:bg-opacity-20 rounded-full transition-colors" title="Refresh All (Ctrl+R)">
                            <i class="fas fa-sync-alt text-lg"></i>
                        </button>
                        <a href="{{ route('api-keys') }}" class="header-btn p-2 hover:bg-white hover:bg-opacity-20 rounded-full transition-colors" title="API Keys Management">
                            <i class="fas fa-key text-lg"></i>
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
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- WhatsApp Connection Card -->
            <div class="bg-white rounded-xl card-shadow p-6">
                <div class="flex items-center mb-6">
                    <i class="fas fa-link text-2xl text-blue-600 mr-3"></i>
                    <h2 class="text-xl font-bold text-gray-800">WhatsApp Connection</h2>
                </div>
                @livewire('whatsapp-connector')
            </div>

            <!-- WhatsApp Manager Card -->
            <div class="bg-white rounded-xl card-shadow p-6">
                <div class="flex items-center mb-6">
                    <i class="fas fa-paper-plane text-2xl text-green-600 mr-3"></i>
                    <h2 class="text-xl font-bold text-gray-800">Message Manager</h2>
                </div>
                @livewire('whatsapp-manager')
            </div>
        </div>

        <!-- Features Section -->
        <div class="mt-12 grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl p-6 text-center card-shadow feature-card">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 feature-icon-bg">
                    <i class="fas fa-qrcode text-2xl text-blue-600"></i>
                </div>
                <h3 class="font-bold text-lg mb-2 text-gray-800">Quick Connect</h3>
                <p class="text-gray-600 text-sm">Scan QR code to connect WhatsApp instantly</p>
            </div>

            <div class="bg-white rounded-xl p-6 text-center card-shadow feature-card">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 feature-icon-bg">
                    <i class="fas fa-check-circle text-2xl text-green-600"></i>
                </div>
                <h3 class="font-bold text-lg mb-2 text-gray-800">Number Validation</h3>
                <p class="text-gray-600 text-sm">Check if numbers exist on WhatsApp</p>
            </div>

            <div class="bg-white rounded-xl p-6 text-center card-shadow feature-card">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4 feature-icon-bg">
                    <i class="fas fa-rocket text-2xl text-purple-600"></i>
                </div>
                <h3 class="font-bold text-lg mb-2 text-gray-800">Fast Delivery</h3>
                <p class="text-gray-600 text-sm">Send messages quickly and reliably</p>
            </div>

            <div class="bg-white rounded-xl p-6 text-center card-shadow feature-card">
                <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4 feature-icon-bg">
                    <i class="fas fa-robot text-2xl text-orange-600"></i>
                </div>
                <h3 class="font-bold text-lg mb-2 text-gray-800">AI Integration</h3>
                <p class="text-gray-600 text-sm">API keys for AI agents and automation</p>
            </div>
        </div>

        <!-- Message Reader -->
        <div class="mt-8 bg-white rounded-xl card-shadow p-6">
            <div class="flex items-center mb-6">
                <i class="fas fa-envelope-open-text text-2xl text-purple-600 mr-3"></i>
                <h2 class="text-xl font-bold text-gray-800">Message Reader</h2>
                <span class="ml-2 text-sm bg-purple-100 text-purple-800 px-2 py-1 rounded-full">AI Ready</span>
            </div>
            @livewire('message-reader')
        </div>

        <!-- Real-time Status -->
        <div class="mt-8 bg-white rounded-xl card-shadow p-6">
            <h3 class="text-lg font-bold mb-4">
                <i class="fas fa-chart-line mr-2"></i>
                Real-time Status
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600" id="messages-sent">0</div>
                    <div class="text-sm text-gray-600">Messages Sent</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-green-600" id="numbers-checked">0</div>
                    <div class="text-sm text-gray-600">Numbers Checked</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-purple-600" id="success-rate">100%</div>
                    <div class="text-sm text-gray-600">Success Rate</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-orange-600" id="uptime">00:00:00</div>
                    <div class="text-sm text-gray-600">Uptime</div>
                </div>
            </div>
        </div>
    </main>

    @livewireScripts

    <script>
        // Socket.IO Connection with auto-reconnect
        let socket;
        let reconnectAttempts = 0;
        let maxReconnectAttempts = 5;
        let startTime = Date.now();
        const whatsappEngineUrl = '{{ env("WHATSAPP_ENGINE_URL", "http://localhost:3000") }}';

        function connectSocket() {
            socket = io(whatsappEngineUrl, {
                transports: ['websocket', 'polling'],
                timeout: 5000,
                forceNew: true
            });

            socket.on('connect', () => {
                console.log('✅ Connected to WhatsApp Engine');
                reconnectAttempts = 0;
                updateConnectionIndicator('connected');
            });

            socket.on('disconnect', (reason) => {
                console.log('❌ Disconnected from WhatsApp Engine:', reason);
                updateConnectionIndicator('disconnected');

                // Auto-reconnect
                if (reconnectAttempts < maxReconnectAttempts) {
                    setTimeout(() => {
                        reconnectAttempts++;
                        console.log(`🔄 Reconnecting... (${reconnectAttempts}/${maxReconnectAttempts})`);
                        connectSocket();
                    }, 2000 * reconnectAttempts);
                }
            });

            socket.on('status', (status) => {
                console.log('📊 Status update:', status);
                updateConnectionIndicator(status);
                Livewire.dispatch('statusUpdated', {
                    status: status
                });
            });

            socket.on('qr', (qr) => {
                console.log('📱 QR update:', qr ? 'Received' : 'Cleared');
                Livewire.dispatch('qrUpdated', {
                    qr: qr
                });
            });

            socket.on('connect_error', (error) => {
                console.error('Socket connection error:', error);
                updateConnectionIndicator('error');
            });
        }

        // Update connection indicator
        function updateConnectionIndicator(status) {
            const indicator = document.getElementById('connection-status');
            const dot = indicator.querySelector('.status-indicator');
            const text = indicator.querySelector('span:last-child');

            dot.className = 'status-indicator mr-2';

            switch (status) {
                case 'connected':
                    dot.classList.add('status-connected');
                    text.textContent = 'Connected';
                    indicator.className = 'flex items-center text-sm bg-green-500 bg-opacity-30 px-3 py-2 rounded-full';
                    break;
                case 'qr':
                    dot.classList.add('status-qr');
                    text.textContent = 'Scan QR Code';
                    indicator.className = 'flex items-center text-sm bg-yellow-500 bg-opacity-30 px-3 py-2 rounded-full';
                    break;
                case 'disconnected':
                    dot.classList.add('status-disconnected');
                    text.textContent = 'Disconnected';
                    indicator.className = 'flex items-center text-sm bg-red-500 bg-opacity-30 px-3 py-2 rounded-full';
                    break;
                default:
                    dot.classList.add('status-disconnected');
                    text.textContent = 'Connection Error';
                    indicator.className = 'flex items-center text-sm bg-red-500 bg-opacity-30 px-3 py-2 rounded-full';
            }
        }

        // Update current time
        function updateTime() {
            const now = new Date();
            document.getElementById('current-time').textContent = now.toLocaleString();
        }

        // Update uptime
        function updateUptime() {
            const uptime = Date.now() - startTime;
            const hours = Math.floor(uptime / 3600000);
            const minutes = Math.floor((uptime % 3600000) / 60000);
            const seconds = Math.floor((uptime % 60000) / 1000);

            document.getElementById('uptime').textContent =
                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }

        // Header button functions
        function refreshAll() {
            console.log('🔄 Refreshing all components...');

            // Visual feedback
            const btn = document.getElementById('refresh-all');
            const icon = btn.querySelector('i');
            icon.classList.add('fa-spin');
            btn.disabled = true;

            // Refresh actions
            Livewire.dispatch('refresh-all');
            connectSocket(); // Reconnect socket

            // Reset button after animation
            setTimeout(() => {
                icon.classList.remove('fa-spin');
                btn.disabled = false;
            }, 1500);
        }

        // Add keyboard shortcuts
        function setupKeyboardShortcuts() {
            document.addEventListener('keydown', function(e) {
                // Ctrl/Cmd + R for refresh (prevent default browser refresh)
                if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                    e.preventDefault();
                    refreshAll();
                }

            });
        }

        // Initialize
        connectSocket();
        updateTime();
        setInterval(updateTime, 1000);
        setInterval(updateUptime, 1000);
        setupKeyboardShortcuts();

        // Add event listeners for header buttons
        document.getElementById('refresh-all').addEventListener('click', refreshAll);

        // Livewire event handlers
        document.addEventListener('livewire:initialized', () => {
            // Handle Livewire errors
            Livewire.hook('request', ({
                fail
            }) => {
                fail(({
                    status,
                    content,
                    preventDefault
                }) => {
                    console.error('Livewire Request Failed:', {
                        status,
                        content
                    });

                    if (status === 500) {
                        console.error('Server Error - Check console for details');
                    }
                });
            });

            // Handle WhatsApp connection events
            Livewire.on('whatsapp-connected', () => {
                console.log('🎉 WhatsApp connected!');
                // You can add notifications or other actions here
            });

            Livewire.on('whatsapp-disconnected', () => {
                console.log('📱 WhatsApp disconnected');
                // You can add notifications or other actions here
            });

            // Handle status errors
            Livewire.on('status-error', (data) => {
                console.error('Status Error:', data);
            });

            // Auto-hide messages after 5 seconds
            Livewire.on('message-shown', () => {
                setTimeout(() => {
                    Livewire.dispatch('clear-message');
                }, 5000);
            });

            // Handle refresh all
            Livewire.on('refresh-all', () => {
                console.log('📡 Refreshing all Livewire components...');
                // This will trigger a refresh of all Livewire components
                window.location.reload();
            });
        });
    </script>
</body>

</html>