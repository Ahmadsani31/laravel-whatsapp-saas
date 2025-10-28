<div class="space-y-6">
    <!-- Mode Selector -->
    <div class="flex space-x-4 mb-6">
        <button wire:click="switchMode('single')"
            class="px-4 py-2 rounded-lg font-medium transition-colors {{ $checkMode === 'single' ? 'bg-blue-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
            <i class="fas fa-phone mr-2"></i>
            Single Number
        </button>
        <button wire:click="switchMode('bulk')"
            class="px-4 py-2 rounded-lg font-medium transition-colors {{ $checkMode === 'bulk' ? 'bg-blue-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
            <i class="fas fa-list mr-2"></i>
            Bulk Check
        </button>
    </div>

    @if ($checkMode === 'single')
        <!-- Single Number Input -->
        <div>
            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                <i class="fas fa-phone mr-1"></i>
                Phone Number
            </label>
            <div class="flex space-x-2">
                <input type="text" wire:model="number" placeholder="e.g., +1234567890"
                    class="flex-1 px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    dir="ltr">

                <button wire:click="checkNumber" wire:loading.attr="disabled"
                    class="px-6 py-3 bg-blue-500 dark:bg-blue-600 text-white rounded-lg hover:bg-blue-600 dark:hover:bg-blue-500 transition-colors disabled:opacity-50">
                    <span wire:loading.remove wire:target="checkNumber">
                        <i class="fas fa-search mr-1"></i>
                        Check
                    </span>
                    <span wire:loading wire:target="checkNumber">
                        <i class="fas fa-spinner fa-spin mr-1"></i>
                        Checking...
                    </span>
                </button>
            </div>
            @error('number')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    @else
        <!-- Bulk Numbers Input -->
        <div>
            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                <i class="fas fa-list mr-1"></i>
                Phone Numbers (Multiple)
            </label>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <i class="fas fa-info-circle mr-1"></i>
                Supports: new lines, commas, or semicolons as separators
                @if ($numbers)
                    <span
                        class="ml-2 px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded text-xs font-medium">
                        {{ count(array_filter(array_map('trim', preg_split('/[\r\n,;]+/', $numbers)))) }}
                        numbers detected
                    </span>
                @endif
            </div>
            <div class="space-y-3">
                <textarea wire:model="numbers" rows="8"
                    placeholder="Enter phone numbers (one per line, or separated by commas):&#10;+1234567890&#10;+0987654321&#10;+1122334455"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none font-mono text-sm"
                    dir="ltr"></textarea>

                <div class="items-center">
                    <button wire:click="checkBulkNumbers" wire:loading.attr="disabled"
                        class="px-6 py-3 bg-green-500 dark:bg-green-600 text-white rounded-lg hover:bg-green-600 dark:hover:bg-green-500 transition-colors disabled:opacity-50 font-medium">
                        <span wire:loading.remove wire:target="checkBulkNumbers">
                            <i class="fas fa-search mr-2"></i>
                            Check All Numbers
                        </span>
                        <span wire:loading wire:target="checkBulkNumbers">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Checking Numbers...
                        </span>
                    </button>

                </div>

                <!-- Bulk Loading Progress -->
                <div wire:loading wire:target="checkBulkNumbers"
                    class="mt-4 p-4 bg-blue-50 dark:bg-blue-900 rounded-lg border border-blue-200 dark:border-blue-700">
                    <div class="flex items-center justify-center space-x-3">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 dark:border-blue-400">
                        </div>
                        <div class="text-blue-800 dark:text-blue-200">
                            <span class="font-medium">Processing numbers...</span>
                            <div class="text-sm text-blue-600 dark:text-blue-300 mt-1">
                                This may take a few moments depending on the number of entries
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @error('numbers')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <!-- Single Check Result -->
    @if ($checkMode === 'single' && $checkResult)
        <div
            class="p-4 rounded-lg border-l-4 {{ isset($checkResult['exists']) && $checkResult['exists'] ? 'border-green-500 bg-green-50 dark:bg-green-900' : 'border-red-500 bg-red-50 dark:bg-red-900' }}">
            <div class="flex items-center">
                <i
                    class="fas {{ isset($checkResult['exists']) && $checkResult['exists'] ? 'fa-check-circle text-green-600 dark:text-green-400' : 'fa-times-circle text-red-600 dark:text-red-400' }} mr-2"></i>
                <span
                    class="{{ isset($checkResult['exists']) && $checkResult['exists'] ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200' }}">
                    {{ $checkResult['message'] ?? ($checkResult['error'] ?? 'Unknown result') }}
                </span>
            </div>
        </div>
    @endif

    <!-- Bulk Check Results -->
    @if ($checkMode === 'bulk' && !empty($bulkCheckResults))
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <!-- Results Header -->
            <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Bulk Check Results
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Total: {{ count($bulkCheckResults) }} numbers |
                            Valid: <span
                                class="text-green-600 dark:text-green-400 font-medium">{{ count(array_filter($bulkCheckResults, fn($r) => $r['exists'] ?? false)) }}</span>
                            |
                            Invalid: <span
                                class="text-red-600 dark:text-red-400 font-medium">{{ count(array_filter($bulkCheckResults, fn($r) => !($r['exists'] ?? false))) }}</span>
                        </p>
                    </div>

                    <button wire:click="exportResults"
                        class="px-4 py-2 bg-blue-500 dark:bg-blue-600 text-white rounded-lg hover:bg-blue-600 dark:hover:bg-blue-500 transition-colors text-sm font-medium">
                        <i class="fas fa-download mr-2"></i>
                        Export CSV
                    </button>
                </div>
            </div>

            <!-- Results Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                #
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Phone Number
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Status
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Message
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Checked At
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($bulkCheckResults as $index => $result)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-mono text-sm text-gray-900 dark:text-gray-100">
                                        {{ $result['number'] ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if (isset($result['exists']))
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full {{ $result['exists'] ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' }}">
                                            <i
                                                class="fas {{ $result['exists'] ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                            {{ $result['exists'] ? 'Valid' : 'Invalid' }}
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Error
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs">
                                    <div class="truncate" title="{{ $result['message'] ?? 'N/A' }}">
                                        {{ $result['message'] ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $result['checked_at'] ?? 'N/A' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Message Input -->
    <div>
        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
            <i class="fas fa-comment mr-1"></i>
            Message Text
        </label>
        <textarea wire:model="message" rows="4" placeholder="Type your message here..."
            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
        @error('message')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <!-- Send Button -->
    <button wire:click="sendMessage" wire:loading.attr="disabled"
        class="w-full px-6 py-4 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors disabled:opacity-50 font-bold text-lg">
        <span wire:loading.remove wire:target="sendMessage">
            <i class="fas fa-paper-plane mr-2"></i>
            Send Message
        </span>
        <span wire:loading wire:target="sendMessage">
            <i class="fas fa-spinner fa-spin mr-2"></i>
            Sending...
        </span>
    </button>

    <!-- Send Result -->
    @if ($sendResult)
        <div
            class="p-4 rounded-lg border-l-4 {{ isset($sendResult['success']) && $sendResult['success'] ? 'border-green-500 bg-green-50 dark:bg-green-900' : 'border-red-500 bg-red-50 dark:bg-red-900' }}">
            <div class="flex items-center">
                <i
                    class="fas {{ isset($sendResult['success']) && $sendResult['success'] ? 'fa-check-circle text-green-600 dark:text-green-400' : 'fa-times-circle text-red-600 dark:text-red-400' }} mr-2"></i>
                <span
                    class="{{ isset($sendResult['success']) && $sendResult['success'] ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200' }}">
                    {{ $sendResult['message'] ?? ($sendResult['error'] ?? 'Unknown result') }}
                </span>
            </div>
        </div>
    @endif

    <!-- Quick Actions -->
    <div class="border-t pt-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold text-gray-800 dark:text-gray-200">
                <i class="fas fa-bolt mr-1"></i>
                Quick Actions
            </h3>

            @if (($checkMode === 'single' && $checkResult) || ($checkMode === 'bulk' && !empty($bulkCheckResults)))
                <button wire:click="clearResults"
                    class="px-3 py-1 text-sm bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 hover:bg-red-200 dark:hover:bg-red-800 rounded-lg transition-colors">
                    <i class="fas fa-trash mr-1"></i>
                    Clear Results
                </button>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-3">
            <button wire:click="$set('message', 'Hello! How can I help you?')"
                class="p-3 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg transition-colors">
                <i class="fas fa-hand-wave mr-1"></i>
                Welcome Message
            </button>

            <button wire:click="$set('message', 'Thank you for contacting us!')"
                class="p-3 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg transition-colors">
                <i class="fas fa-heart mr-1"></i>
                Thank You Message
            </button>
        </div>

        @if ($checkMode === 'bulk')
            <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900 rounded-lg">
                <h4 class="font-medium text-blue-800 dark:text-blue-200 mb-2">
                    <i class="fas fa-lightbulb mr-1"></i>
                    Bulk Check Tips
                </h4>
                <ul class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
                    <li>• Enter one phone number per line</li>
                    <li>• You can also separate numbers with commas or semicolons</li>
                    <li>• Include country code (e.g., +1, +966, +20)</li>
                    <li>• Duplicate numbers will be automatically removed</li>
                    <li>• Results can be exported as CSV for further analysis</li>
                </ul>
            </div>
        @endif
    </div>
</div>
