<div>
    <!-- Success/Error Messages -->
    @if($message)
        <div class="mb-4 p-3 rounded-lg {{ $messageType === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800' }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas {{ $messageType === 'success' ? 'fa-check-circle text-green-500' : 'fa-exclamation-triangle text-red-500' }} mr-2"></i>
                    <span class="text-sm font-medium">{{ $message }}</span>
                </div>
                <button wire:click="clearMessage" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Conversations List -->
        <div class="lg:col-span-1">
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-800">Recent Conversations</h3>
                    <button wire:click="refreshConversations" 
                            class="text-blue-600 hover:text-blue-800 text-sm"
                            wire:loading.attr="disabled">
                        <i class="fas fa-sync-alt {{ $loading ? 'fa-spin' : '' }}"></i>
                    </button>
                </div>

                @if($loading && empty($conversations))
                    <div class="text-center py-8">
                        <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                        <p class="text-gray-500 text-sm">Loading conversations...</p>
                    </div>
                @elseif(empty($conversations))
                    <div class="text-center py-8">
                        <i class="fas fa-comments text-3xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500 text-sm">Click refresh to load conversations</p>
                        <p class="text-gray-400 text-xs mt-1">Make sure WhatsApp is connected</p>
                        <button wire:click="refreshConversations" 
                                class="mt-3 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-sync-alt mr-2"></i> Load Conversations
                        </button>
                    </div>
                @else
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        @foreach($conversations as $conversation)
                            <div wire:click="selectConversation('{{ $conversation['number'] }}')"
                                 class="p-3 rounded-lg cursor-pointer transition-colors {{ $selectedNumber === $conversation['number'] ? 'bg-blue-100 border border-blue-300' : 'bg-white hover:bg-gray-50 border border-gray-200' }}">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                                <i class="fas fa-user text-green-600 text-sm"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-gray-900 truncate">
                                                    {{ $conversation['name'] }}
                                                </p>
                                                <p class="text-xs text-gray-500 truncate">
                                                    {{ $conversation['number'] }}
                                                </p>
                                            </div>
                                        </div>
                                        @if(!empty($conversation['lastMessage']))
                                            <p class="text-sm text-gray-600 mt-1 truncate">
                                                {{ $conversation['lastMessageFromMe'] ? 'You: ' : '' }}{{ $conversation['lastMessage'] }}
                                            </p>
                                        @endif
                                    </div>
                                    @if($conversation['unreadCount'] > 0)
                                        <span class="bg-red-500 text-white text-xs rounded-full px-2 py-1 ml-2">
                                            {{ $conversation['unreadCount'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Messages Display -->
        <div class="lg:col-span-2">
            @if(empty($selectedNumber))
                <div class="bg-gray-50 rounded-lg p-8 text-center">
                    <i class="fas fa-comments text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-700 mb-2">Select a Conversation</h3>
                    <p class="text-gray-500">Choose a conversation from the left to view messages</p>
                </div>
            @else
                <div class="bg-white rounded-lg border border-gray-200">
                    <!-- Chat Header -->
                    <div class="border-b border-gray-200 p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-green-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $selectedNumber }}</h3>
                                    <p class="text-sm text-gray-500">WhatsApp Chat</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button wire:click="refreshMessages" 
                                        class="text-blue-600 hover:text-blue-800 p-2 rounded-full hover:bg-blue-50"
                                        wire:loading.attr="disabled">
                                    <i class="fas fa-sync-alt {{ $loading ? 'fa-spin' : '' }}"></i>
                                </button>
                                <button wire:click="clearSelection" 
                                        class="text-gray-600 hover:text-gray-800 p-2 rounded-full hover:bg-gray-50">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div class="p-4 max-h-96 overflow-y-auto">
                        @if($loading && empty($messages))
                            <div class="text-center py-8">
                                <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                                <p class="text-gray-500 text-sm">Loading messages...</p>
                            </div>
                        @elseif(empty($messages))
                            <div class="text-center py-8">
                                <i class="fas fa-comment text-3xl text-gray-300 mb-3"></i>
                                <p class="text-gray-500 text-sm">No messages found</p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($messages as $msg)
                                    <div class="flex {{ $msg['fromMe'] ? 'justify-end' : 'justify-start' }}">
                                        <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg {{ $msg['fromMe'] ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800' }}">
                                            <p class="text-sm">{{ $msg['message'] }}</p>
                                            <p class="text-xs mt-1 {{ $msg['fromMe'] ? 'text-blue-100' : 'text-gray-500' }}">
                                                {{ \Carbon\Carbon::createFromTimestamp($msg['timestamp'])->format('M j, H:i') }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    // Auto-clear messages after 3 seconds
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('message-shown', () => {
            setTimeout(() => {
                Livewire.dispatch('clearMessage');
            }, 3000);
        });
    });
</script>