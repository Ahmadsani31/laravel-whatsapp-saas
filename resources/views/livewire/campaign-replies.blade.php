<div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <div class="flex items-center mb-2">
                <a href="{{ route('campaigns.details', $campaign->id) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Campaign Replies</h1>
            </div>
            <p class="text-gray-600 dark:text-gray-400">Replies for campaign: <strong>{{ $campaign->name }}</strong></p>
        </div>
        
        <div class="flex space-x-3">
            @if($stats['unprocessed'] > 0)
            <button wire:click="markAllAsProcessed" 
                wire:confirm="Are you sure you want to mark all replies as processed?"
                class="bg-blue-600 dark:bg-blue-700 text-white px-4 py-2 rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors">
                <i class="fas fa-check-double mr-2"></i>
                Mark All Processed
            </button>
            @endif
            
            <button wire:click="exportReplies" 
                class="bg-green-600 dark:bg-green-700 text-white px-4 py-2 rounded-lg hover:bg-green-700 dark:hover:bg-green-600 transition-colors">
                <i class="fas fa-download mr-2"></i>
                Export CSV
            </button>
        </div>
    </div>

    <!-- Reply Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['total'] }}</div>
            <div class="text-sm text-blue-800 dark:text-blue-200">Total Replies</div>
        </div>
        
        <div class="bg-green-50 dark:bg-green-900 p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['processed'] }}</div>
            <div class="text-sm text-green-800 dark:text-green-200">Processed</div>
        </div>
        
        <div class="bg-orange-50 dark:bg-orange-900 p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $stats['unprocessed'] }}</div>
            <div class="text-sm text-orange-800 dark:text-orange-200">Unprocessed</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 mb-6 card-shadow">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div class="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-4">
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filter by Status</label>
                    <select wire:model.live="filterProcessed" 
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="all">All Replies</option>
                        <option value="processed">Processed</option>
                        <option value="unprocessed">Unprocessed</option>
                    </select>
                </div>

                <!-- Phone Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search Phone</label>
                    <input type="text" wire:model.live.debounce.300ms="searchPhone" 
                        placeholder="Search phone number..."
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Content Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search Content</label>
                    <input type="text" wire:model.live.debounce.300ms="searchContent" 
                        placeholder="Search reply content..."
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <div class="text-sm text-gray-600 dark:text-gray-400">
                Showing {{ $replies->count() }} of {{ $replies->total() }} replies
            </div>
        </div>
    </div>

    <!-- Replies List -->
    <div class="bg-white dark:bg-gray-800 rounded-lg card-shadow overflow-hidden">
        @if($replies->count() > 0)
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($replies as $reply)
            <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center mb-2">
                            <span class="font-mono text-sm font-medium text-gray-900 dark:text-gray-100 mr-4">
                                {{ $reply->phone_number }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $reply->received_at->format('M j, Y H:i:s') }}
                            </span>
                            @if($reply->is_processed)
                            <span class="ml-2 inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                <i class="fas fa-check mr-1"></i>
                                Processed
                            </span>
                            @else
                            <span class="ml-2 inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200">
                                <i class="fas fa-clock mr-1"></i>
                                Unprocessed
                            </span>
                            @endif
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-3">
                            <p class="text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ $reply->message_content }}</p>
                        </div>

                        @if($reply->campaignMessage)
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <strong>Original message:</strong> 
                            <span class="italic">{{ Str::limit($reply->campaignMessage->message_content, 100) }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="ml-4 flex flex-col space-y-2">
                        @if(!$reply->is_processed)
                        <button wire:click="markAsProcessed({{ $reply->id }})" 
                            class="px-3 py-1 bg-blue-600 dark:bg-blue-700 text-white rounded text-sm hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors">
                            <i class="fas fa-check mr-1"></i>
                            Mark Processed
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $replies->links() }}
        </div>
        @else
        <div class="text-center py-12">
            <i class="fas fa-comments text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">No Replies Found</h3>
            <p class="text-gray-500 dark:text-gray-400">
                @if($filterProcessed !== 'all' || !empty($searchPhone) || !empty($searchContent))
                Try adjusting your filters to see more results.
                @else
                This campaign hasn't received any replies yet.
                @endif
            </p>
        </div>
        @endif
    </div>
</div>