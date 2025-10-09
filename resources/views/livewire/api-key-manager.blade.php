
<div>
    <!-- Success/Error Messages -->
    @if($message)
    <div class="mb-6 p-4 rounded-xl {{ $messageType === 'success' ? 'bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-200' }} card-shadow">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas {{ $messageType === 'success' ? 'fa-check-circle text-green-500 dark:text-green-400' : 'fa-exclamation-triangle text-red-500 dark:text-red-400' }} mr-3"></i>
                <span class="font-medium">{{ $message }}</span>
            </div>
            <button wire:click="clearMessage" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    @endif

    <!-- New API Key Display (Only shown once after creation) -->
    @if($showNewKey && $newApiKey)
    <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-xl p-6 mb-8 card-shadow">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-500 dark:text-green-400 text-2xl"></i>
            </div>
            <div class="ml-4 flex-1">
                <h3 class="text-lg font-bold text-green-800 dark:text-green-200 mb-3">API Key Created Successfully!</h3>
                <div class="space-y-2 text-sm text-green-700 dark:text-green-300">
                    <div><strong>Name:</strong> {{ $newApiKey['name'] }}</div>
                    <div><strong>Permissions:</strong> {{ empty($newApiKey['permissions']) ? 'All (*)' : implode(', ', $newApiKey['permissions']) }}</div>
                    <div><strong>Expires:</strong> {{ $newApiKey['expires_at'] }}</div>
                    <div class="mt-3">
                        <strong>API Key:</strong>
                        <div class="mt-1 p-3 bg-gray-900 dark:bg-gray-800 rounded-lg border border-gray-700">
                            <code class="text-green-400 dark:text-green-300 text-xs font-mono break-all select-all">{{ $newApiKey['key'] }}</code>
                        </div>
                    </div>
                </div>
                <div class="mt-4 p-3 bg-red-100 dark:bg-red-900 border border-red-300 dark:border-red-700 rounded-lg">
                    <p class="text-red-800 dark:text-red-200 font-medium text-sm">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Save this key securely - it will not be shown again!
                    </p>
                </div>
                <button wire:click="dismissNewKey" class="mt-3 text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 text-sm font-medium">
                    <i class="fas fa-times mr-1"></i> Dismiss
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Create API Key Form -->
    <div class="bg-white dark:bg-gray-800 rounded-xl card-shadow mb-8">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <i class="fas fa-plus-circle text-2xl text-blue-600 dark:text-blue-400 mr-3"></i>
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">Create New API Key</h2>
            </div>
        </div>
        <div class="p-6">
            <form wire:submit="createApiKey" class="space-y-4">
                <div>
                    <label for="newKeyName" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Name <span class="text-red-500 dark:text-red-400">*</span>
                    </label>
                    <input type="text"
                        id="newKeyName"
                        wire:model="newKeyName"
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="e.g., My AI Agent">
                    @error('newKeyName')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="newKeyPermissions" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Permissions (optional)
                    </label>
                    <input type="text"
                        id="newKeyPermissions"
                        wire:model="newKeyPermissions"
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="whatsapp_send,whatsapp_check,whatsapp_status">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Leave empty for all permissions, or comma-separated list (e.g., whatsapp_send,whatsapp_status)
                    </p>
                    @error('newKeyPermissions')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="newKeyExpiresAt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Expires At (optional)
                    </label>
                    <input type="datetime-local"
                        id="newKeyExpiresAt"
                        wire:model="newKeyExpiresAt"
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Leave empty for no expiration</p>
                    @error('newKeyExpiresAt')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="bg-blue-600 dark:bg-blue-700 text-white px-6 py-2 rounded-md hover:bg-blue-700 dark:hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors">
                    <i class="fas fa-key mr-2"></i>
                    Create API Key
                </button>
            </form>
        </div>
    </div>

    <!-- API Keys List -->
    <div class="bg-white dark:bg-gray-800 rounded-xl card-shadow">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-list text-2xl text-green-600 dark:text-green-400 mr-3"></i>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">Existing API Keys</h2>
                </div>
                <button wire:click="loadApiKeys" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300" title="Refresh">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>

        @if(empty($apiKeys))
        <div class="p-8 text-center text-gray-500 dark:text-gray-400">
            <i class="fas fa-key text-4xl mb-4 opacity-50"></i>
            <p class="text-lg font-medium">No API Keys Found</p>
            <p class="text-sm">Create your first API key using the form above.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Key</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Last Used</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Expires</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($apiKeys as $key)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $key['name'] }}</div>
                            @if(!empty($key['permissions']))
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                Permissions: {{ implode(', ', $key['permissions']) }}
                            </div>
                            @else
                            <div class="text-xs text-gray-500 dark:text-gray-400">All permissions (*)</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-xs text-gray-700 dark:text-gray-300">{{ $key['masked_key'] }}</code>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $key['is_active'] ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' }}">
                                <i class="fas {{ $key['is_active'] ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                {{ $key['is_active'] ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $key['formatted_last_used'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $key['formatted_expires'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $key['formatted_created'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <button wire:click="toggleApiKey({{ $key['id'] }})"
                                class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 transition-colors"
                                title="{{ $key['is_active'] ? 'Deactivate' : 'Activate' }}">
                                <i class="fas {{ $key['is_active'] ? 'fa-pause' : 'fa-play' }}"></i>
                                {{ $key['is_active'] ? 'Disable' : 'Enable' }}
                            </button>
                            <button wire:click="deleteApiKey({{ $key['id'] }})"
                                wire:confirm="Are you sure you want to delete the API key '{{ $key['name'] }}'? This action cannot be undone."
                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition-colors"
                                title="Delete">
                                <i class="fas fa-trash"></i>
                                Delete
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Auto-clear messages after 5 seconds
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('message-shown', () => {
            setTimeout(() => {
                Livewire.dispatch('clearMessage');
            }, 5000);
        });
    });
</script>
@endpush