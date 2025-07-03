<?php
    // =============================================================================
    // FICHIER 5: resources/views/partials/settings-heading.blade.php
    // =============================================================================
?>
<div class="mb-6">
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Paramètres</h1>
    </div>

    <!-- Navigation des paramètres -->
    <nav class="flex space-x-8">
        <a href="{{ route('settings.profile') }}"
           class="py-2 px-1 border-b-2 font-medium text-sm {{ request()->routeIs('settings.profile') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            Profil
        </a>
        <a href="{{ route('settings.password') }}"
           class="py-2 px-1 border-b-2 font-medium text-sm {{ request()->routeIs('settings.password') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            Mot de passe
        </a>
        <a href="{{ route('settings.appearance') }}"
           class="py-2 px-1 border-b-2 font-medium text-sm {{ request()->routeIs('settings.appearance') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            Apparence
        </a>
    </nav>
</div>
