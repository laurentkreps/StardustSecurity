<?php
    // =============================================================================
    // FICHIER 4: resources/views/components/settings/layout.blade.php
    // =============================================================================
?>
@props(['heading', 'subheading' => ''])

<div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-8">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $heading }}</h2>
                    @if($subheading)
                        <p class="mt-1 text-sm text-gray-600">{{ $subheading }}</p>
                    @endif
                </div>

                {{ $slot }}
            </div>
        </div>
    </div>
</div>
