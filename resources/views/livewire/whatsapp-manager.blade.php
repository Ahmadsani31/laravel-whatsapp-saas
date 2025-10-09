<div class="space-y-6">
    <!-- Number Input -->
    <div>
        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
            <i class="fas fa-phone mr-1"></i>
            Phone Number
        </label>
        <div class="flex space-x-2">
            <input type="text" 
                wire:model="number" 
                placeholder="e.g., +1234567890"
                class="flex-1 px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                dir="ltr">
            
            <button wire:click="checkNumber" 
                wire:loading.attr="disabled"
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

    <!-- Check Result -->
    @if($checkResult)
        <div class="p-4 rounded-lg border-l-4 
            {{ isset($checkResult['exists']) && $checkResult['exists'] ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50' }}">
            <div class="flex items-center">
                <i class="fas {{ isset($checkResult['exists']) && $checkResult['exists'] ? 'fa-check-circle text-green-600' : 'fa-times-circle text-red-600' }} mr-2"></i>
                <span class="{{ isset($checkResult['exists']) && $checkResult['exists'] ? 'text-green-800' : 'text-red-800' }}">
                    {{ $checkResult['message'] ?? $checkResult['error'] ?? 'Unknown result' }}
                </span>
            </div>
        </div>
    @endif

    <!-- Message Input -->
    <div>
        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
            <i class="fas fa-comment mr-1"></i>
            Message Text
        </label>
        <textarea wire:model="message" 
            rows="4" 
            placeholder="Type your message here..."
            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
        @error('message') 
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> 
        @enderror
    </div>

    <!-- Send Button -->
    <button wire:click="sendMessage" 
        wire:loading.attr="disabled"
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
    @if($sendResult)
        <div class="p-4 rounded-lg border-l-4 
            {{ isset($sendResult['success']) && $sendResult['success'] ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50' }}">
            <div class="flex items-center">
                <i class="fas {{ isset($sendResult['success']) && $sendResult['success'] ? 'fa-check-circle text-green-600' : 'fa-times-circle text-red-600' }} mr-2"></i>
                <span class="{{ isset($sendResult['success']) && $sendResult['success'] ? 'text-green-800' : 'text-red-800' }}">
                    {{ $sendResult['message'] ?? $sendResult['error'] ?? 'Unknown result' }}
                </span>
            </div>
        </div>
    @endif

    <!-- Quick Actions -->
    <div class="border-t pt-6">
        <h3 class="font-bold text-gray-800 dark:text-gray-200 mb-3">
            <i class="fas fa-bolt mr-1"></i>
            Quick Actions
        </h3>
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
    </div>

    <!-- Statistics -->
    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
        <h4 class="font-bold text-gray-800 dark:text-gray-200 mb-2">
            <i class="fas fa-chart-bar mr-1"></i>
            Quick Stats
        </h4>
        <div class="grid grid-cols-3 gap-4 text-center">
            <div>
                <div class="text-2xl font-bold text-blue-600">0</div>
                <div class="text-xs text-gray-600 dark:text-gray-400">Messages Sent</div>
            </div>
            <div>
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">0</div>
                <div class="text-xs text-gray-600 dark:text-gray-400">Numbers Checked</div>
            </div>
            <div>
                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">100%</div>
                <div class="text-xs text-gray-600 dark:text-gray-400">Success Rate</div>
            </div>
        </div>
    </div>
</div>