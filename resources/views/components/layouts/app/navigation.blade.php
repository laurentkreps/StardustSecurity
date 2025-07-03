<?php
    // =============================================================================
    // FICHIER 2: resources/views/components/layouts/app/navigation.blade.php
    // =============================================================================
?>
<nav class="bg-white shadow-sm border-b border-gray-200 fixed w-full z-30 lg:pl-64">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Mobile menu button -->
                <button type="button" class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <h1 class="text-xl font-semibold text-gray-900 ml-4 lg:ml-0">
                    Gestion Multi-Normes
                </h1>
            </div>

            <!-- User menu -->
            <div class="flex items-center space-x-4">
                <!-- Notifications -->
                <button class="p-1 rounded-full text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 2.384 14.51a.75.75 0 0 0-.22.53v5.25c0 .414.336.75.75.75h5.25c.199 0 .389-.079.53-.22l5.093-5.093L18.262 10.253a.235.235 0 0 0 .022-.02L21.457 6.96a.75.75 0 0 0 0-1.06l-3.337-3.337a.75.75 0 0 0-1.06 0L13.787 5.826z"/>
                    </svg>
                </button>

                <!-- User dropdown -->
                <div class="relative">
                    <button class="flex items-center text-sm rounded-full text-gray-500 hover:text-gray-700">
                        <div class="h-8 w-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                            {{ auth()->user()->initials() }}
                        </div>
                        <span class="ml-2 hidden md:block">{{ auth()->user()->name }}</span>
                        <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown menu (vous pouvez utiliser Alpine.js ou Livewire pour la logique) -->
                    <div class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                        <a href="{{ route('settings.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil</a>
                        <a href="{{ route('settings.password') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Mot de passe</a>
                        <a href="{{ route('settings.appearance') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Apparence</a>
                        <div class="border-t border-gray-100"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
