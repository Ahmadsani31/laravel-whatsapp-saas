<div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <div class="flex items-center mb-2">
                <a href="{{ route('campaigns') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $campaign->name }}</h1>
                <span class="ml-4 inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full bg-{{ $campaign->status_color }}-100 dark:bg-{{ $campaign->status_color }}-900 text-{{ $campaign->status_color }}-800 dark:text-{{ $campaign->status_color }}-200">
                    {{ ucfirst($campaign->status) }}
                </span>
            </div>
            @if($campaign->description)
            <p class="text-gray-600 dark:text-gray-400">{{ $campaign->description }}</p>
            @endif
        </div>
        
        <div class="flex flex-wrap gap-3">
            <button wire:click="refreshStats" 
                class="bg-blue-600 dark:bg-blue-700 text-white px-4 py-2 rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors">
                <i class="fas fa-sync-alt mr-2"></i>
                Refresh Stats
            </button>
            
            @if($campaign->failed_count > 0)
            <button wire:click="retryFailedMessages" 
                wire:confirm="Are you sure you want to retry all failed messages?"
                class="bg-orange-600 dark:bg-orange-700 text-white px-4 py-2 rounded-lg hover:bg-orange-700 dark:hover:bg-orange-600 transition-colors">
                <i class="fas fa-redo mr-2"></i>
                Retry Failed
            </button>
            @endif

            @if($campaign->reply_count > 0)
            <a href="{{ route('campaigns.replies', $campaign->id) }}" 
                class="bg-purple-600 dark:bg-purple-700 text-white px-4 py-2 rounded-lg hover:bg-purple-700 dark:hover:bg-purple-600 transition-colors">
                <i class="fas fa-comments mr-2"></i>
                View Replies ({{ $campaign->reply_count }})
            </a>
            @endif
            
            <button wire:click="exportResults" 
                class="bg-green-600 dark:bg-green-700 text-white px-4 py-2 rounded-lg hover:bg-green-700 dark:hover:bg-green-600 transition-colors">
                <i class="fas fa-download mr-2"></i>
                Export CSV
            </button>
        </div>
    </div>

    <!-- Campaign Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4 mb-8">
        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Total</div>
        </div>
        
        <div class="bg-yellow-50 dark:bg-yellow-900 p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['pending'] }}</div>
            <div class="text-sm text-yellow-800 dark:text-yellow-200">Pending</div>
        </div>
        
        <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['sent'] }}</div>
            <div class="text-sm text-blue-800 dark:text-blue-200">Sent</div>
        </div>
        
        <div class="bg-green-50 dark:bg-green-900 p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['delivered'] }}</div>
            <div class="text-sm text-green-800 dark:text-green-200">Delivered</div>
        </div>
        
        <div class="bg-purple-50 dark:bg-purple-900 p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['read'] }}</div>
            <div class="text-sm text-purple-800 dark:text-purple-200">Read</div>
        </div>
        
        <div class="bg-orange-50 dark:bg-orange-900 p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $stats['replies'] }}</div>
            <div class="text-sm text-orange-800 dark:text-orange-200">Replies</div>
        </div>
        
        <div class="bg-red-50 dark:bg-red-900 p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['failed'] }}</div>
            <div class="text-sm text-red-800 dark:text-red-200">Failed</div>
        </div>
        
        <div class="bg-indigo-50 dark:bg-indigo-900 p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['reply_rate'] }}%</div>
            <div class="text-sm text-indigo-800 dark:text-indigo-200">Reply Rate</div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 mb-8 card-shadow">
        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
            <span>Campaign Progress</span>
            <span>{{ $stats['progress_percentage'] }}% Complete</span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
            <div class="bg-gradient-to-r from-blue-500 to-green-500 h-3 rounded-full transition-all duration-500" 
                style="width: {{ $stats['progress_percentage'] }}%"></div>
        </div>
        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-2">
            <span>{{ $stats['sent'] + $stats['failed'] }} / {{ $stats['total'] }} processed</span>
            <span>Success Rate: {{ $stats['success_rate'] }}%</span>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 mb-6 card-shadow">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div class="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-4">
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filter by Status</label>
                    <select wire:model.live="filterStatus" 
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="sent">Sent</option>
                        <option value="delivered">Delivered</option>
                        <option value="read">Read</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>

                <!-- Phone Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search Phone</label>
                    <input type="text" wire:model.live.debounce.300ms="searchPhone" 
                        placeholder="Search phone number..."
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <div class="text-sm text-gray-600 dark:text-gray-400">
                Showing {{ $messages->count() }} of {{ $messages->total() }} messages
            </div>
        </div>
    </div>

    <!-- Messages List -->
    <div class="bg-white dark:bg-gray-800 rounded-lg card-shadow overflow-hidden">
        @if($messages->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Phone Number
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Sent At
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Delivered At
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Read At
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Replies
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Error
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($messages as $message)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-mono text-sm text-gray-900 dark:text-gray-100">
                                {{ $message->phone_number }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-{{ $message->status_color }}-100 dark:bg-{{ $message->status_color }}-900 text-{{ $message->status_color }}-800 dark:text-{{ $message->status_color }}-200">
                                <i class="fas fa-{{ $message->status_icon }} mr-1"></i>
                                {{ ucfirst($message->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $message->sent_at ? $message->sent_at->format('M j, H:i') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $message->delivered_at ? $message->delivered_at->format('M j, H:i') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $message->read_at ? $message->read_at->format('M j, H:i') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            @if($message->replies->count() > 0)
                            <button wire:click="viewReplies({{ $message->id }})" 
                                class="text-orange-600 dark:text-orange-400 hover:text-orange-800 dark:hover:text-orange-300 font-medium">
                                <i class="fas fa-comments mr-1"></i>
                                {{ $message->replies->count() }} {{ $message->replies->count() == 1 ? 'Reply' : 'Replies' }}
                            </button>
                            @else
                            -
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs">
                            @if($message->error_message)
                            <div class="truncate" title="{{ $message->error_message }}">
                                {{ $message->error_message }}
                            </div>
                            @else
                            -
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $messages->links() }}
        </div>
        @else
        <div class="text-center py-12">
            <i class="fas fa-inbox text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">No Messages Found</h3>
            <p class="text-gray-500 dark:text-gray-400">
                @if($filterStatus !== 'all' || !empty($searchPhone))
                Try adjusting your filters to see more results.
                @else
                This campaign doesn't have any messages yet.
                @endif
            </p>
        </div>
        @endif
    </div>

    <!-- Campaign Info -->
    <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg p-6 card-shadow">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Campaign Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Message Content</h4>
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ $campaign->message_content }}</p>
                </div>
            </div>
            <div>
                <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Timeline</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Created:</span>
                        <span class="text-gray-900 dark:text-gray-100">{{ $campaign->created_at->format('M j, Y H:i') }}</span>
                    </div>
                    @if($campaign->started_at)
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Started:</span>
                        <span class="text-gray-900 dark:text-gray-100">{{ $campaign->started_at->format('M j, Y H:i') }}</span>
                    </div>
                    @endif
                    @if($campaign->completed_at)
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Completed:</span>
                        <span class="text-gray-900 dark:text-gray-100">{{ $campaign->completed_at->format('M j, Y H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Replies Modal -->
    @if($showReplies)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-2xl mx-4 max-h-[80vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Message Replies</h2>
                <button wire:click="hideReplies" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            @if(count($selectedMessageReplies) > 0)
            <div class="space-y-4">
                @foreach($selectedMessageReplies as $reply)
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border-l-4 border-orange-500">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Reply #{{ $reply['id'] }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $reply['received_at'] }}</span>
                    </div>
                    <p class="text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ $reply['message_content'] }}</p>
                    @if($reply['is_processed'])
                    <div class="mt-2">
                        <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                            <i class="fas fa-check mr-1"></i>
                            Processed
                        </span>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8">
                <i class="fas fa-comments text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
                <p class="text-gray-500 dark:text-gray-400">No replies found for this message.</p>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>