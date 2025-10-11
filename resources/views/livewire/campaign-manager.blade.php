<div>
    <!-- Success/Error Messages -->
    @if($message)
    <div class="mb-6 p-4 rounded-xl {{ $messageType_alert === 'success' ? 'bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-200' }} card-shadow">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas {{ $messageType_alert === 'success' ? 'fa-check-circle text-green-500 dark:text-green-400' : 'fa-exclamation-triangle text-red-500 dark:text-red-400' }} mr-3"></i>
                <span class="font-medium">{{ $message }}</span>
            </div>
            <button wire:click="clearMessage" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    @endif

    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Campaign Manager</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Create and manage WhatsApp marketing campaigns</p>
        </div>
        <button wire:click="showCreateCampaign" 
            class="bg-blue-600 dark:bg-blue-700 text-white px-6 py-3 rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors font-medium">
            <i class="fas fa-plus mr-2"></i>
            New Campaign
        </button>
    </div>

    <!-- Create Campaign Modal -->
    @if($showCreateForm)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Campaign</h2>
                <button wire:click="hideCreateForm" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form wire:submit="createCampaign" class="space-y-6">
                <!-- Campaign Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Campaign Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model="name" 
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., Summer Sale 2024">
                    @error('name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Description
                    </label>
                    <textarea wire:model="description" rows="3"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                        placeholder="Brief description of your campaign..."></textarea>
                    @error('description') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <!-- Message Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Message Type <span class="text-red-500">*</span>
                    </label>
                    <div class="flex space-x-4">
                        <label class="flex items-center">
                            <input type="radio" wire:model="messageType" value="text" class="mr-2">
                            <span class="text-gray-700 dark:text-gray-300">Text Message</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" wire:model="messageType" value="template" class="mr-2">
                            <span class="text-gray-700 dark:text-gray-300">Template Message</span>
                        </label>
                    </div>
                </div>

                <!-- Template Name (if template selected) -->
                @if($messageType === 'template')
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Template Name
                    </label>
                    <input type="text" wire:model="templateName" 
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., welcome_message">
                </div>
                @endif

                <!-- Message Content -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Message Content <span class="text-red-500">*</span>
                    </label>
                    <textarea wire:model="messageContent" rows="4"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                        placeholder="Enter your message content here..."></textarea>
                    @error('messageContent') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <!-- Phone Numbers -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Phone Numbers <span class="text-red-500">*</span>
                    </label>
                    <textarea wire:model="phoneNumbers" rows="6"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none font-mono text-sm"
                        placeholder="Enter phone numbers (one per line):&#10;+1234567890&#10;+9876543210&#10;+1122334455"></textarea>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Enter one phone number per line. Include country code (e.g., +1234567890)
                    </p>
                    @error('phoneNumbers') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <!-- Schedule (Optional) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Schedule Campaign (Optional)
                    </label>
                    <input type="datetime-local" wire:model="scheduledAt"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Leave empty to create as draft
                    </p>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" wire:click="hideCreateForm"
                        class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-6 py-3 bg-blue-600 dark:bg-blue-700 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Create Campaign
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Edit Campaign Modal -->
    @if($showEditForm && $editingCampaign)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Campaign: {{ $editingCampaign->name }}</h2>
                <button wire:click="hideEditForm" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form wire:submit="updateCampaign" class="space-y-6">
                <!-- Campaign Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Campaign Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model="name" 
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., Summer Sale 2024">
                    @error('name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Description
                    </label>
                    <textarea wire:model="description" rows="3"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                        placeholder="Brief description of your campaign..."></textarea>
                    @error('description') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <!-- Message Content -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Message Content <span class="text-red-500">*</span>
                    </label>
                    <textarea wire:model="messageContent" rows="4"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                        placeholder="Enter your message content here..."></textarea>
                    @error('messageContent') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <!-- Phone Numbers -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Phone Numbers <span class="text-red-500">*</span>
                    </label>
                    <textarea wire:model="phoneNumbers" rows="6"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none font-mono text-sm"
                        placeholder="Enter phone numbers (one per line):&#10;+1234567890&#10;+9876543210&#10;+1122334455"></textarea>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Enter one phone number per line. Include country code (e.g., +1234567890)
                    </p>
                    @error('phoneNumbers') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <!-- Warning -->
                <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
                    <div class="flex">
                        <i class="fas fa-exclamation-triangle text-yellow-400 mr-3 mt-0.5"></i>
                        <div>
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Important Notes:</h3>
                            <ul class="mt-2 text-sm text-yellow-700 dark:text-yellow-300 list-disc list-inside space-y-1">
                                <li><strong>Draft/Paused:</strong> Only pending messages will be updated</li>
                                <li><strong>Completed/Failed:</strong> All messages will be reset and recreated</li>
                                <li><strong>Running:</strong> Campaign will be paused during editing</li>
                                <li><strong>Phone Numbers:</strong> Adding/removing numbers will update message list accordingly</li>
                                <li><strong>Statistics:</strong> Completed campaigns will have their stats reset when edited</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" wire:click="hideEditForm"
                        class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-6 py-3 bg-indigo-600 dark:bg-indigo-700 text-white rounded-lg hover:bg-indigo-700 dark:hover:bg-indigo-600 transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Update Campaign
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Campaign Details Modal -->
    @if($showCampaignDetails && $selectedCampaign)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-4xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $selectedCampaign->name }}</h2>
                <button wire:click="hideCampaignDetails" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Campaign Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $selectedCampaign->total_recipients }}</div>
                    <div class="text-sm text-blue-800 dark:text-blue-200">Total Recipients</div>
                </div>
                <div class="bg-green-50 dark:bg-green-900 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $selectedCampaign->sent_count }}</div>
                    <div class="text-sm text-green-800 dark:text-green-200">Sent</div>
                </div>
                <div class="bg-purple-50 dark:bg-purple-900 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $selectedCampaign->delivered_count }}</div>
                    <div class="text-sm text-purple-800 dark:text-purple-200">Delivered</div>
                </div>
                <div class="bg-indigo-50 dark:bg-indigo-900 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $selectedCampaign->read_count }}</div>
                    <div class="text-sm text-indigo-800 dark:text-indigo-200">Read</div>
                </div>
            </div>

            <!-- Messages List -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Message Details</h3>
                <div class="max-h-64 overflow-y-auto">
                    @foreach($selectedCampaign->messages as $message)
                    <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-600 last:border-b-0">
                        <div class="flex items-center">
                            <span class="font-mono text-sm text-gray-700 dark:text-gray-300">{{ $message->phone_number }}</span>
                        </div>
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-{{ $message->status_color }}-100 dark:bg-{{ $message->status_color }}-900 text-{{ $message->status_color }}-800 dark:text-{{ $message->status_color }}-200">
                                <i class="fas fa-{{ $message->status_icon }} mr-1"></i>
                                {{ ucfirst($message->status) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Campaigns List -->
    @if(empty($campaigns))
    <div class="empty-state">
        <div class="empty-state-card">
            <i class="fas fa-bullhorn empty-state-icon text-blue-400 dark:text-blue-500"></i>
            <h3 class="text-3xl font-bold text-gray-700 dark:text-gray-300 mb-4">No Campaigns Yet</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-8 text-lg">Create your first marketing campaign to get started</p>
            <button wire:click="showCreateCampaign" class="action-btn action-btn-start">
                <i class="fas fa-plus mr-2"></i>
                Create First Campaign
            </button>
        </div>
    </div>
    @else
    <div class="space-y-6">
        @foreach($campaigns as $campaign)
        <div class="bg-white dark:bg-gray-800 rounded-xl card-shadow p-6 border border-gray-200 dark:border-gray-700">
            <div class="p-8">
                <div class="flex items-start justify-between">

                    <div class="flex-1">
                        <!-- Campaign Header -->
                        <div class="flex items-center mb-6">
                            <div class="campaign-icon mr-6">
                                <i class="fas fa-bullhorn text-white text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $campaign['name'] }}</h3>
                                <div class="flex items-center">
                                    <span class="status-badge status-badge-{{ $campaign['status'] }}">
                                        <i class="fas fa-circle mr-2 text-xs"></i>
                                        {{ ucfirst($campaign['status']) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        @if($campaign['description'])
                        <p class="text-gray-600 dark:text-gray-400 mb-6 text-lg leading-relaxed">{{ $campaign['description'] }}</p>
                        @endif

                        <!-- Progress Bar -->
                        <div class="mb-8">
                            <div class="flex justify-between text-sm font-semibold text-gray-600 dark:text-gray-400 mb-3">
                                <span>Campaign Progress</span>
                                <span>{{ $campaign['progress_percentage'] }}%</span>
                            </div>
                            <div class="progress-container">
                                <div class="progress-bar" style="width: {{ $campaign['progress_percentage'] }}%"></div>
                            </div>
                        </div>

                        <!-- Stats Grid -->
                        <div class="grid grid-cols-3 md:grid-cols-6 gap-4 mb-8">
                            <div class="stat-card stat-card-total">
                                <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ number_format($campaign['total_recipients']) }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Total</div>
                            </div>
                            <div class="stat-card stat-card-sent">
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400 mb-1">{{ number_format($campaign['sent_count']) }}</div>
                                <div class="text-xs text-blue-800 dark:text-blue-200 font-medium">Sent</div>
                            </div>
                            <div class="stat-card stat-card-delivered">
                                <div class="text-2xl font-bold text-green-600 dark:text-green-400 mb-1">{{ number_format($campaign['delivered_count']) }}</div>
                                <div class="text-xs text-green-800 dark:text-green-200 font-medium">Delivered</div>
                            </div>
                            <div class="stat-card stat-card-read">
                                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 mb-1">{{ number_format($campaign['read_count']) }}</div>
                                <div class="text-xs text-purple-800 dark:text-purple-200 font-medium">Read</div>
                            </div>
                            <div class="stat-card stat-card-replies">
                                <div class="text-2xl font-bold text-orange-600 dark:text-orange-400 mb-1">{{ number_format($campaign['reply_count'] ?? 0) }}</div>
                                <div class="text-xs text-orange-800 dark:text-orange-200 font-medium">Replies</div>
                            </div>
                            <div class="stat-card stat-card-failed">
                                <div class="text-2xl font-bold text-red-600 dark:text-red-400 mb-1">{{ number_format($campaign['failed_count']) }}</div>
                                <div class="text-xs text-red-800 dark:text-red-200 font-medium">Failed</div>
                            </div>
                        </div>

                        <!-- Timestamps -->
                        <div class="text-sm text-gray-500 dark:text-gray-400 space-y-2 bg-gray-50 dark:bg-gray-700 rounded-2xl p-4">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-plus mr-3 text-blue-500"></i>
                                <span><strong>Created:</strong> {{ $campaign['created_at'] }}</span>
                            </div>
                            @if($campaign['started_at'])
                            <div class="flex items-center">
                                <i class="fas fa-play mr-3 text-green-500"></i>
                                <span><strong>Started:</strong> {{ $campaign['started_at'] }}</span>
                            </div>
                            @endif
                            @if($campaign['completed_at'])
                            <div class="flex items-center">
                                <i class="fas fa-check-circle mr-3 text-purple-500"></i>
                                <span><strong>Completed:</strong> {{ $campaign['completed_at'] }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions Panel -->
                    <div class="ml-8 flex flex-col space-y-3 min-w-[180px]">
                        <!-- Control Buttons -->
                        @if($campaign['can_start'])
                        <button wire:click="startCampaign({{ $campaign['id'] }})" wire:confirm="Are you sure you want to start this campaign?" class="action-btn action-btn-start">
                            <i class="fas fa-play mr-2"></i>
                            Start Campaign
                        </button>
                        @endif

                        @if($campaign['can_pause'])
                        <button wire:click="pauseCampaign({{ $campaign['id'] }})" class="action-btn action-btn-pause">
                            <i class="fas fa-pause mr-2"></i>
                            Pause Campaign
                        </button>
                        @endif

                        @if($campaign['can_stop'])
                        <button wire:click="stopCampaign({{ $campaign['id'] }})" wire:confirm="Are you sure you want to stop this campaign?" class="action-btn action-btn-stop">
                            <i class="fas fa-stop mr-2"></i>
                            Stop Campaign
                        </button>
                        @endif

                        @if($campaign['can_restart'])
                        <button wire:click="restartCampaign({{ $campaign['id'] }})" wire:confirm="Are you sure you want to restart this campaign? All messages will be reset and sent again." class="action-btn action-btn-restart">
                            <i class="fas fa-redo mr-2"></i>
                            Restart
                        </button>
                        @endif

                        <!-- Quick Access -->
                        <a href="{{ route('campaigns.details', $campaign['id']) }}" class="action-btn action-btn-details text-center">
                            <i class="fas fa-eye mr-2"></i>
                            View Details
                        </a>

                        @if($campaign['reply_count'] > 0)
                        <a href="{{ route('campaigns.replies', $campaign['id']) }}" class="action-btn action-btn-replies text-center">
                            <i class="fas fa-comments mr-2"></i>
                            Replies ({{ $campaign['reply_count'] }})
                        </a>
                        @endif

                        <!-- More Actions -->
                        @if($campaign['can_edit'])
                        <button wire:click="showEditCampaign({{ $campaign['id'] }})" class="action-btn action-btn-edit">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Campaign
                        </button>
                        @endif

                        <a href="{{ route('campaigns.auto-replies', $campaign['id']) }}" class="action-btn action-btn-auto-replies text-center">
                            <i class="fas fa-robot mr-2"></i>
                            Auto Replies
                        </a>

                        <button wire:click="cloneCampaign({{ $campaign['id'] }})" wire:confirm="Are you sure you want to clone this campaign? A copy will be created as a draft." class="action-btn action-btn-clone">
                            <i class="fas fa-copy mr-2"></i>
                            Clone Campaign
                        </button>

                        <button wire:click="deleteCampaign({{ $campaign['id'] }})" wire:confirm="Are you sure you want to delete this campaign? This action cannot be undone." class="action-btn action-btn-delete">
                            <i class="fas fa-trash mr-2"></i>
                            Delete Campaign
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
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