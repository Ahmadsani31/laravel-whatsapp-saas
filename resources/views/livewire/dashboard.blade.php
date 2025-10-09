<div>
    <!-- Dashboard Status Bar -->
    <div class="mb-6 bg-white rounded-lg shadow-sm p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <x-status-indicator />
                <div class="text-sm text-gray-500">
                    <i class="fas fa-clock mr-1"></i>
                    <span id="current-time"></span>
                </div>
            </div>
            <button id="refresh-all" class="btn-refresh px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                <i class="fas fa-sync-alt mr-1"></i>
                Refresh All
            </button>
        </div>
    </div>

    <!-- Main Dashboard Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- WhatsApp Connection Card -->
        <x-dashboard-card title="WhatsApp Connection" icon="link" icon-color="blue">
            @livewire('whatsapp-connector')
        </x-dashboard-card>

        <!-- WhatsApp Manager Card -->
        <x-dashboard-card title="Message Manager" icon="paper-plane" icon-color="green">
            @livewire('whatsapp-manager')
        </x-dashboard-card>
    </div>

    <!-- Message Reader -->
    <x-dashboard-card title="Message Reader" icon="envelope-open-text" icon-color="purple">
        <div class="flex items-center justify-between mb-4">
            <span class="text-sm bg-purple-100 text-purple-800 px-2 py-1 rounded-full">AI Ready</span>
        </div>
        @livewire('message-reader', ['embedded' => true])
    </x-dashboard-card>

</div>

@push('styles')
<meta name="whatsapp-engine-url" content="{{ env('WHATSAPP_ENGINE_URL', 'http://localhost:3000') }}">
@endpush