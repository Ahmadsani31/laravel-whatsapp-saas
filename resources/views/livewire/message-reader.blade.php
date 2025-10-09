<div>
    <!-- Success/Error Messages -->
    @if($message)
    <div class="mb-6 p-4 rounded-lg {{ $messageType === 'success' ? 'bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-200' }}">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas {{ $messageType === 'success' ? 'fa-check-circle text-green-500 dark:text-green-400' : 'fa-exclamation-triangle text-red-500 dark:text-red-400' }} ml-2"></i>
                <span class="font-medium">{{ $message }}</span>
            </div>
            <button wire:click="clearMessage" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 p-1">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Conversations List -->
        <div class="lg:col-span-1">
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200">Recent Conversations</h3>
                    <button wire:click="refreshConversations"
                        class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm"
                        wire:loading.attr="disabled">
                        <i class="fas fa-sync-alt {{ $loading ? 'fa-spin' : '' }}"></i>
                    </button>
                </div>

                @if($loading && empty($conversations))
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-2xl text-gray-400 dark:text-gray-500 mb-2"></i>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Loading conversations...</p>
                </div>
                @elseif(empty($conversations))
                <div class="text-center py-8">
                    <i class="fas fa-comments text-3xl text-gray-300 dark:text-gray-600 mb-3"></i>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Click refresh to load conversations</p>
                    <p class="text-gray-400 dark:text-gray-500 text-xs mt-1">Make sure WhatsApp is connected</p>
                    <button wire:click="refreshConversations"
                        class="mt-3 bg-blue-600 dark:bg-blue-700 text-white px-4 py-2 rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors">
                        <i class="fas fa-sync-alt mr-2"></i> Load Conversations
                    </button>
                </div>
                @else
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @foreach($conversations as $conversation)
                    <div wire:click="selectConversation('{{ $conversation['number'] }}')"
                        class="p-3 rounded-lg cursor-pointer transition-colors {{ $selectedNumber === $conversation['number'] ? 'bg-blue-100 dark:bg-blue-900 border border-blue-300 dark:border-blue-700' : 'bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-600' }}">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-user text-green-600 dark:text-green-400 text-sm"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 dark:text-gray-100 truncate">
                                            {{ $conversation['name'] }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                            {{ $conversation['number'] }}
                                        </p>
                                    </div>
                                </div>
                                @if(!empty($conversation['lastMessage']))
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1 truncate">
                                    {{ $conversation['lastMessageFromMe'] ? 'You: ' : '' }}{{ $conversation['lastMessage'] }}
                                </p>
                                @endif
                            </div>
                            @if($conversation['unreadCount'] > 0)
                            <span class="bg-red-500 dark:bg-red-600 text-white text-xs rounded-full px-2 py-1 ml-2">
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
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-8 text-center border border-gray-200 dark:border-gray-600">
                <i class="fas fa-comments text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">Select a Conversation</h3>
                <p class="text-gray-500 dark:text-gray-400">Choose a conversation from the left to view messages</p>
            </div>
            @else
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                <!-- Chat Header -->
                <div class="border-b border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-user text-green-600 dark:text-green-400"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $selectedNumber }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">WhatsApp Chat</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button wire:click="refreshMessages"
                                class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 p-2 rounded-full hover:bg-blue-50 dark:hover:bg-blue-900"
                                wire:loading.attr="disabled">
                                <i class="fas fa-sync-alt {{ $loading ? 'fa-spin' : '' }}"></i>
                            </button>
                            <button wire:click="clearSelection"
                                class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 p-2 rounded-full hover:bg-gray-50 dark:hover:bg-gray-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <div class="p-4 max-h-96 overflow-y-auto custom-scrollbar">
                    @if($loading && empty($messages))
                    <div class="text-center py-8">
                        <i class="fas fa-spinner fa-spin text-2xl text-gray-400 dark:text-gray-500 mb-2"></i>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Loading messages...</p>
                    </div>
                    @elseif(empty($messages))
                    <div class="text-center py-8">
                        <i class="fas fa-comment text-3xl text-gray-300 dark:text-gray-600 mb-3"></i>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">No messages found</p>
                    </div>
                    @else
                    <div class="space-y-3">
                        @foreach($messages as $msg)
                        <div class="flex {{ $msg['fromMe'] ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg {{ $msg['fromMe'] ? 'bg-blue-500 dark:bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200' }}">
                                <p class="text-sm">{{ $msg['message'] }}</p>
                                <p class="text-xs mt-1 {{ $msg['fromMe'] ? 'text-blue-100 dark:text-blue-200' : 'text-gray-500 dark:text-gray-400' }}">
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