<div>
    <!-- Dashboard Status Bar -->
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <x-status-indicator />
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <i class="fas fa-clock mr-1"></i>
                    <span id="current-time"></span>
                </div>
            </div>
            <button id="refresh-all" class="btn-refresh px-3 py-2 bg-blue-600 dark:bg-blue-700 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors text-sm">
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
            <span class="text-sm bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-2 py-1 rounded-full border border-purple-200 dark:border-purple-700">AI Ready</span>
        </div>
        @livewire('message-reader', ['embedded' => true])
    </x-dashboard-card>

</div>

