@props(['title', 'icon', 'iconColor' => 'blue'])

<div class="bg-white rounded-lg card-shadow p-6">
    <div class="flex items-center mb-6">
        <i class="fas fa-{{ $icon }} text-2xl text-{{ $iconColor }}-600 mr-3"></i>
        <h2 class="text-xl font-bold text-gray-800">{{ $title }}</h2>
    </div>
    {{ $slot }}
</div>