<div class="space-y-4">
    <!-- Status Display -->
    <div class="flex items-center justify-between p-4 rounded-lg border-2 
        {{ $status === 'connected' ? 'border-green-200 bg-green-50' : 
           ($status === 'qr' ? 'border-yellow-200 bg-yellow-50' : 'border-red-200 bg-red-50') }}">
        
        <div class="flex items-center space-x-3">
            @if($status === 'connected')
                <i class="fas fa-check-circle text-2xl text-green-600"></i>
                <div>
                    <h3 class="font-bold text-green-800">Connected</h3>
                    <p class="text-sm text-green-600">WhatsApp is connected and ready to use</p>
                </div>
            @elseif($status === 'qr')
                <i class="fas fa-qrcode text-2xl text-yellow-600 pulse-animation"></i>
                <div>
                    <h3 class="font-bold text-yellow-800">Waiting for QR Scan</h3>
                    <p class="text-sm text-yellow-600">Scan the QR code with your phone</p>
                </div>
            @else
                <i class="fas fa-times-circle text-2xl text-red-600"></i>
                <div>
                    <h3 class="font-bold text-red-800">Disconnected</h3>
                    <p class="text-sm text-red-600">Attempting to connect...</p>
                </div>
            @endif
        </div>

        <div class="flex space-x-2">
            <button wire:click="getStatus" 
                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                <i class="fas fa-sync-alt mr-1"></i>
                Refresh
            </button>
            
            @if($status === 'connected')
                <button wire:click="disconnect" 
                    class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                    <i class="fas fa-sign-out-alt mr-1"></i>
                    Disconnect
                </button>
            @endif
        </div>
    </div>

    <!-- Message Display -->
    @if($message)
        <div class="p-4 rounded-lg border-l-4 
            {{ $messageType === 'success' ? 'border-green-500 bg-green-50 text-green-800' : 
               ($messageType === 'error' ? 'border-red-500 bg-red-50 text-red-800' : 
                ($messageType === 'warning' ? 'border-yellow-500 bg-yellow-50 text-yellow-800' :
                 'border-blue-500 bg-blue-50 text-blue-800')) }}">
            <div class="flex items-center">
                <i class="fas {{ $messageType === 'success' ? 'fa-check-circle' : 
                                ($messageType === 'error' ? 'fa-exclamation-circle' : 
                                 ($messageType === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle')) }} mr-2"></i>
                {{ $message }}
            </div>
        </div>
    @endif

    <!-- QR Code Display -->
    @if($status === 'qr' && $qr)
        <div class="text-center p-6 bg-white border-2 border-dashed border-gray-300 rounded-lg">
            <h3 class="text-lg font-bold mb-4 text-gray-800">Scan QR Code</h3>
            <div class="inline-block p-4 bg-white rounded-lg shadow-lg">
                <img src="{{ $qr }}" alt="QR Code" class="w-64 h-64 mx-auto">
            </div>
            <p class="mt-4 text-sm text-gray-600">
                <i class="fas fa-mobile-alt mr-1"></i>
                Open WhatsApp on your phone → Settings → Linked Devices → Link a Device
            </p>
        </div>
    @endif

    <!-- Connection Instructions -->
    @if($status === 'disconnected')
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <h4 class="font-bold text-blue-800 mb-2">
                <i class="fas fa-info-circle mr-1"></i>
                How to Connect
            </h4>
            <ol class="text-sm text-blue-700 space-y-1 list-decimal list-inside">
                <li>Make sure the WhatsApp engine is running</li>
                <li>Wait for the QR code to appear</li>
                <li>Open WhatsApp on your phone</li>
                <li>Go to Settings → Linked Devices</li>
                <li>Tap "Link a Device" and scan the code</li>
            </ol>
        </div>
    @endif

    <!-- Auto-refresh indicator -->
    @if($autoRefresh)
        <div class="text-center text-xs text-gray-500">
            <i class="fas fa-sync-alt fa-spin mr-1"></i>
            Auto-refreshing status...
        </div>
    @endif
</div>