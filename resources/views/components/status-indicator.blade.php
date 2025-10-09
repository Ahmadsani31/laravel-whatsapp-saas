@props(['status' => 'disconnected', 'text' => 'Disconnected'])

<div id="connection-status" class="flex items-center text-sm bg-gray-100 px-3 py-2 rounded-full">
    <span class="status-indicator status-{{ $status }}"></span>
    <span class="font-medium">{{ $text }}</span>
</div>