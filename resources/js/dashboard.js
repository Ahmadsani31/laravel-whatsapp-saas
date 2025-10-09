import { Config } from './config';

// Dashboard JavaScript Functions
class DashboardManager {
    constructor() {
        this.socket = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.startTime = Date.now();
        this.whatsappEngineUrl = Config.getWhatsAppEngineUrl();
        
        if (Config.isDebug()) {
            console.log('🔗 WhatsApp Engine URL:', this.whatsappEngineUrl);
            console.log('🎨 Theme Preference:', Config.getThemePreference());
        }
        this.init();
    }

    init() {
        this.connectSocket();
        this.updateTime();
        this.setupEventListeners();
        this.setupLivewireEvents();
        
        // Update time every second
        setInterval(() => this.updateTime(), 1000);
    }

    connectSocket() {
        this.socket = io(this.whatsappEngineUrl, {
            transports: ['websocket', 'polling'],
            timeout: 5000,
            forceNew: true
        });

        this.socket.on('connect', () => {
            if (Config.isDebug()) {
                console.log('✅ Connected to WhatsApp Engine');
            }
            this.reconnectAttempts = 0;
            this.updateConnectionIndicator('connected');
        });

        this.socket.on('disconnect', (reason) => {
            if (Config.isDebug()) {
                console.log('❌ Disconnected from WhatsApp Engine:', reason);
            }
            this.updateConnectionIndicator('disconnected');
            this.handleReconnect();
        });

        this.socket.on('status', (status) => {
            if (Config.isDebug()) {
                console.log('📊 Status update:', status);
            }
            this.updateConnectionIndicator(status);
            Livewire.dispatch('statusUpdated', { status });
        });

        this.socket.on('qr', (qr) => {
            if (Config.isDebug()) {
                console.log('📱 QR update:', qr ? 'Received' : 'Cleared');
            }
            Livewire.dispatch('qrUpdated', { qr });
        });

        this.socket.on('connect_error', (error) => {
            console.error('Socket connection error:', error);
            this.updateConnectionIndicator('error');
        });
    }

    handleReconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            setTimeout(() => {
                this.reconnectAttempts++;
                console.log(`🔄 Reconnecting... (${this.reconnectAttempts}/${this.maxReconnectAttempts})`);
                this.connectSocket();
            }, 2000 * this.reconnectAttempts);
        }
    }

    updateConnectionIndicator(status) {
        const indicator = document.getElementById('connection-status');
        if (!indicator) return;

        const dot = indicator.querySelector('.status-indicator');
        const text = indicator.querySelector('span:last-child');

        if (!dot || !text) return;

        dot.className = 'status-indicator';

        const statusConfig = {
            connected: {
                class: 'status-connected',
                text: 'Connected',
                bgClass: 'flex items-center text-sm bg-green-100 px-3 py-2 rounded-full text-green-800'
            },
            qr: {
                class: 'status-qr',
                text: 'Scan QR Code',
                bgClass: 'flex items-center text-sm bg-yellow-100 px-3 py-2 rounded-full text-yellow-800'
            },
            disconnected: {
                class: 'status-disconnected',
                text: 'Disconnected',
                bgClass: 'flex items-center text-sm bg-gray-100 px-3 py-2 rounded-full text-gray-800'
            },
            default: {
                class: 'status-disconnected',
                text: 'Connection Error',
                bgClass: 'flex items-center text-sm bg-red-100 px-3 py-2 rounded-full text-red-800'
            }
        };

        const config = statusConfig[status] || statusConfig.default;
        dot.classList.add(config.class);
        text.textContent = config.text;
        indicator.className = config.bgClass;
    }

    updateTime() {
        const timeElement = document.getElementById('current-time');
        if (timeElement) {
            timeElement.textContent = new Date().toLocaleString();
        }
    }

    refreshAll() {
        console.log('🔄 Refreshing all components...');

        const btn = document.getElementById('refresh-all');
        const icon = btn?.querySelector('i');
        
        if (btn && icon) {
            icon.classList.add('fa-spin');
            btn.disabled = true;
        }

        // Refresh actions
        Livewire.dispatch('refresh-all');
        this.connectSocket();

        // Reset button after animation
        setTimeout(() => {
            if (btn && icon) {
                icon.classList.remove('fa-spin');
                btn.disabled = false;
            }
        }, 1500);
    }

    setupEventListeners() {
        // Refresh button
        const refreshBtn = document.getElementById('refresh-all');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => this.refreshAll());
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                e.preventDefault();
                this.refreshAll();
            }
        });
    }

    setupLivewireEvents() {
        document.addEventListener('livewire:initialized', () => {
            // Handle Livewire errors
            Livewire.hook('request', ({ fail }) => {
                fail(({ status, content }) => {
                    console.error('Livewire Request Failed:', { status, content });
                    if (status === 500) {
                        console.error('Server Error - Check console for details');
                    }
                });
            });

            // WhatsApp connection events
            Livewire.on('whatsapp-connected', () => {
                console.log('🎉 WhatsApp connected!');
            });

            Livewire.on('whatsapp-disconnected', () => {
                console.log('📱 WhatsApp disconnected');
            });

            // Status errors
            Livewire.on('status-error', (data) => {
                console.error('Status Error:', data);
            });

            // Auto-clear messages
            Livewire.on('message-shown', () => {
                setTimeout(() => {
                    Livewire.dispatch('clearMessage');
                }, 3000);
            });

            // Handle refresh all
            Livewire.on('refresh-all', () => {
                console.log('📡 Refreshing all Livewire components...');
                window.location.reload();
            });
        });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new DashboardManager();
});