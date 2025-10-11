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

    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <div class="flex items-center mb-2">
                <a href="{{ route('campaigns.details', $campaign->id) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Auto Reply Manager</h1>
            </div>
            <p class="text-gray-600 dark:text-gray-400">Manage automatic replies for campaign: <strong>{{ $campaign->name }}</strong></p>
        </div>
        
        <div class="flex space-x-3">
            <button wire:click="createDefaultAutoReply" 
                class="bg-green-600 dark:bg-green-700 text-white px-4 py-2 rounded-lg hover:bg-green-700 dark:hover:bg-green-600 transition-colors">
                <i class="fas fa-magic mr-2"></i>
                Create Default
            </button>
            
            <button wire:click="showCreateAutoReply" 
                class="bg-blue-600 dark:bg-blue-700 text-white px-4 py-2 rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                New Auto Reply
            </button>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['total_auto_replies'] }}</div>
            <div class="text-sm text-blue-800 dark:text-blue-200">Total Auto Replies</div>
        </div>
        
        <div class="bg-green-50 dark:bg-green-900 p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['active_auto_replies'] }}</div>
            <div class="text-sm text-green-800 dark:text-green-200">Active</div>
        </div>
        
        <div class="bg-purple-50 dark:bg-purple-900 p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['successful_sent'] }}</div>
            <div class="text-sm text-purple-800 dark:text-purple-200">Sent Successfully</div>
        </div>
        
        <div class="bg-red-50 dark:bg-red-900 p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['failed_sent'] }}</div>
            <div class="text-sm text-red-800 dark:text-red-200">Failed</div>
        </div>
    </div>

    <!-- Create/Edit Auto Reply Modal -->
    @if($showCreateForm)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $editingAutoReply ? 'Edit Auto Reply' : 'Create Auto Reply' }}
                </h2>
                <button wire:click="hideCreateForm" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form wire:submit="{{ $editingAutoReply ? 'updateAutoReply' : 'createAutoReply' }}" class="space-y-6">
                <!-- Reply Message -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Auto Reply Message <span class="text-red-500">*</span>
                    </label>
                    <textarea wire:model="replyMessage" rows="4"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                        placeholder="Enter the automatic reply message..."></textarea>
                    @error('replyMessage') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <!-- Trigger Keywords -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Trigger Keywords (Optional)
                    </label>
                    <input type="text" wire:model="triggerKeywords" 
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="hello, thanks, support (comma separated)">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Leave empty to reply to all messages. Use comma to separate multiple keywords.
                    </p>
                    @error('triggerKeywords') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <!-- Settings -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Delay -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Delay (seconds)
                        </label>
                        <input type="number" wire:model="delaySeconds" min="0" max="300"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('delaySeconds') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <!-- Active Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Status
                        </label>
                        <div class="flex items-center space-x-4 mt-3">
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="isActive" class="mr-2">
                                <span class="text-gray-700 dark:text-gray-300">Active</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Send Once Per Contact -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="sendOncePerContact" class="mr-2">
                        <span class="text-gray-700 dark:text-gray-300">Send only once per contact</span>
                    </label>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        If enabled, each contact will receive this auto reply only once.
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
                        {{ $editingAutoReply ? 'Update' : 'Create' }} Auto Reply
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Auto Replies List -->
    @if(empty($autoReplies))
    <div class="text-center py-12">
        <i class="fas fa-robot text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
        <h3 class="text-xl font-medium text-gray-700 dark:text-gray-300 mb-2">No Auto Replies Yet</h3>
        <p class="text-gray-500 dark:text-gray-400 mb-6">Create automatic replies to respond to incoming messages</p>
        <button wire:click="createDefaultAutoReply" 
            class="bg-green-600 dark:bg-green-700 text-white px-6 py-3 rounded-lg hover:bg-green-700 dark:hover:bg-green-600 transition-colors mr-4">
            <i class="fas fa-magic mr-2"></i>
            Create Default Auto Reply
        </button>
        <button wire:click="showCreateAutoReply" 
            class="bg-blue-600 dark:bg-blue-700 text-white px-6 py-3 rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Create Custom Auto Reply
        </button>
    </div>
    @else
    <div class="grid gap-6">
        @foreach($autoReplies as $autoReply)
        <div class="bg-white dark:bg-gray-800 rounded-xl card-shadow p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center mb-3">
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full {{ $autoReply['is_active'] ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200' }}">
                                <i class="fas fa-{{ $autoReply['is_active'] ? 'check-circle' : 'pause-circle' }} mr-1"></i>
                                {{ $autoReply['is_active'] ? 'Active' : 'Inactive' }}
                            </span>
                            @if($autoReply['delay_seconds'] > 0)
                            <span class="ml-2 inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                <i class="fas fa-clock mr-1"></i>
                                {{ $autoReply['delay_seconds'] }}s delay
                            </span>
                            @endif
                            @if($autoReply['send_once_per_contact'])
                            <span class="ml-2 inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200">
                                <i class="fas fa-user-check mr-1"></i>
                                Once per contact
                            </span>
                            @endif
                        </div>
                    </div>

                    <!-- Trigger Keywords -->
                    @if($autoReply['trigger_keywords'])
                    <div class="mb-3">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Triggers:</span>
                        <span class="ml-2 text-sm text-gray-800 dark:text-gray-200">{{ $autoReply['trigger_keywords'] }}</span>
                    </div>
                    @else
                    <div class="mb-3">
                        <span class="text-sm font-medium text-orange-600 dark:text-orange-400">
                            <i class="fas fa-globe mr-1"></i>
                            Replies to all messages
                        </span>
                    </div>
                    @endif

                    <!-- Reply Message -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ $autoReply['reply_message'] }}</p>
                    </div>

                    <!-- Timestamps -->
                    <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                        Created: {{ \Carbon\Carbon::parse($autoReply['created_at'])->format('M j, Y H:i') }}
                        @if($autoReply['updated_at'] !== $autoReply['created_at'])
                        • Updated: {{ \Carbon\Carbon::parse($autoReply['updated_at'])->format('M j, Y H:i') }}
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col space-y-2 ml-4">
                    <button wire:click="toggleAutoReply({{ $autoReply['id'] }})" 
                        class="px-3 py-1 {{ $autoReply['is_active'] ? 'bg-orange-600 hover:bg-orange-700' : 'bg-green-600 hover:bg-green-700' }} text-white rounded text-sm transition-colors">
                        <i class="fas fa-{{ $autoReply['is_active'] ? 'pause' : 'play' }} mr-1"></i>
                        {{ $autoReply['is_active'] ? 'Deactivate' : 'Activate' }}
                    </button>

                    <button wire:click="editAutoReply({{ $autoReply['id'] }})" 
                        class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 transition-colors">
                        <i class="fas fa-edit mr-1"></i>
                        Edit
                    </button>

                    <button wire:click="deleteAutoReply({{ $autoReply['id'] }})" 
                        wire:confirm="Are you sure you want to delete this auto reply?"
                        class="px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700 transition-colors">
                        <i class="fas fa-trash mr-1"></i>
                        Delete
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>