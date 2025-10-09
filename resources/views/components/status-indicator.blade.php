@props(['status' => 'disconnected', 'text' => 'Disconnected'])

<div id="connection-status" class="flex items-center text-sm bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-full border border-gray-200 dark:border-gray-600">
    <span class="status-indicator status-{{ $status }}"></span>
    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $text }}</span>
</div>