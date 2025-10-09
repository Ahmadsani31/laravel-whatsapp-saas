@props(['title', 'icon', 'iconColor' => 'blue'])

<div class="bg-white dark:bg-gray-800 rounded-lg card-shadow p-6 border border-gray-200 dark:border-gray-700">
    <div class="flex items-center mb-6">
        <i class="fas fa-{{ $icon }} text-2xl text-{{ $iconColor }}-600 dark:text-{{ $iconColor }}-400 mr-3"></i>
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">{{ $title }}</h2>
    </div>
    {{ $slot }}
</div>