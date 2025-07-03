{{-- resources/views/layouts/app.blade.php --}}
<x-layouts.app :title="$title ?? null">
    @yield('content')
</x-layouts.app>
