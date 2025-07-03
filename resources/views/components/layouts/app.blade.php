<?php
    // =============================================================================
    // FICHIER 1: resources/views/components/layouts/app.blade.php
    // =============================================================================
?>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        <!-- Navigation -->
        @include('components.layouts.app.navigation')

        <!-- Sidebar -->
        @include('components.layouts.app.sidebar')

        <!-- Page Content -->
        <main class="lg:pl-64">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>

<?php