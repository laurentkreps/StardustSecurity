{{-- resources/views/equipment/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Équipements')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Équipements</h1>
                <p class="text-gray-600 mt-1">Gestion des équipements multi-normes (EN 1176, EN 13814, EN 60335)</p>
            </div>
            <a href="{{ route('equipment.create') }}"
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nouvel équipement
            </a>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white p-4 rounded-lg shadow-sm border mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Installation</label>
                <select name="playground_id" class="rounded-md border-gray-300">
                    <option value="">Toutes les installations</option>
                    @foreach($playgrounds as $playground)
                        <option value="{{ $playground->id }}" {{ request('playground_id') == $playground->id ? 'selected' : '' }}>
                            {{ $playground->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                <select name="equipment_category" class="rounded-md border-gray-300">
                    <option value="">Toutes les catégories</option>
                    <option value="playground_equipment" {{ request('equipment_category') === 'playground_equipment' ? 'selected' : '' }}>
                        Équipement aire de jeux
                    </option>
                    <option value="amusement_ride" {{ request('equipment_category') === 'amusement_ride' ? 'selected' : '' }}>
                        Manège/Attraction
                    </option>
                    <option value="electrical_system" {{ request('equipment_category') === 'electrical_system' ? 'selected' : '' }}>
                        Système électrique
                    </option>
                    <option value="infrastructure" {{ request('equipment_category') === 'infrastructure' ? 'selected' : '' }}>
                        Infrastructure
                    </option>
                    <option value="safety_equipment" {{ request('equipment_category') === 'safety_equipment' ? 'selected' : '' }}>
                        Équipement de sécurité
                    </option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                <select name="status" class="rounded-md border-gray-300">
                    <option value="">Tous les statuts</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>En maintenance</option>
                    <option value="out_of_service" {{ request('status') === 'out_of_service' ? 'selected' : '' }}>Hors service</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Référence, type, marque..." class="rounded-md border-gray-300">
            </div>

            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                Filtrer
            </button>

            @if(request()->hasAny(['playground_id', 'equipment_category', 'status', 'search']))
                <a href="{{ route('equipment.index') }}" class="text-gray-500 hover:text-gray-700">
                    Réinitialiser
                </a>
            @endif
        </form>
    </div>

    <!-- Liste des équipements -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        @if($equipment->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Équipement</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catégorie</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Installation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Conformité</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($equipment as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $item->reference_code }}</div>
                                        <div class="text-sm text-gray-500">{{ $item->equipment_type }}</div>
                                        @if($item->brand)
                                            <div class="text-xs text-gray-400">{{ $item->brand }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                                        {{ $item->equipment_category === 'playground_equipment' ? 'bg-blue-100 text-blue-800' :
                                           ($item->equipment_category === 'amusement_ride' ? 'bg-purple-100 text-purple-800' :
                                           ($item->equipment_category === 'electrical_system' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) }}">
                                        @switch($item->equipment_category)
                                            @case('playground_equipment')
                                                Aire de jeux
                                                @break
                                            @case('amusement_ride')
                                                Manège
                                                @break
                                            @case('electrical_system')
                                                Électrique
                                                @break
                                            @case('infrastructure')
                                                Infrastructure
                                                @break
                                            @case('safety_equipment')
                                                Sécurité
                                                @break
                                            @default
                                                {{ $item->equipment_category }}
                                        @endswitch
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div>{{ $item->playground->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->playground->city }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $hasValidCerts = $item->certifications()->valid()->count() > 0;
                                        $needsInspection = $item->equipment_category === 'amusement_ride' &&
                                            !$item->amusementRideInspections()->where('inspection_date', '>=', now()->subMonth())->exists();
                                        $needsElectricalTest = $item->equipment_category === 'electrical_system' &&
                                            (!$item->electrical_test_date || $item->electrical_test_date->addYear()->isPast());
                                    @endphp

                                    @if($hasValidCerts && !$needsInspection && !$needsElectricalTest)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            ✓ Conforme
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            ⚠ Action requise
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                                        {{ $item->status === 'active' ? 'bg-green-100 text-green-800' :
                                           ($item->status === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        @switch($item->status)
                                            @case('active')
                                                Actif
                                                @break
                                            @case('maintenance')
                                                En maintenance
                                                @break
                                            @case('out_of_service')
                                                Hors service
                                                @break
                                            @default
                                                {{ $item->status }}
                                        @endswitch
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('equipment.show', $item) }}"
                                           class="text-blue-600 hover:text-blue-900">Voir</a>
                                        <a href="{{ route('equipment.edit', $item) }}"
                                           class="text-indigo-600 hover:text-indigo-900">Modifier</a>
                                        @if($item->equipment_category === 'amusement_ride')
                                            <a href="{{ route('inspections.create.equipment', $item) }}"
                                               class="text-purple-600 hover:text-purple-900">Inspecter</a>
                                        @endif
                                        @if($item->equipment_category === 'electrical_system')
                                            <a href="{{ route('electrical-tests.create.equipment', $item) }}"
                                               class="text-green-600 hover:text-green-900">Tester</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $equipment->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun équipement trouvé</h3>
                <p class="mt-1 text-sm text-gray-500">Commencez par ajouter votre premier équipement.</p>
                <div class="mt-6">
                    <a href="{{ route('equipment.create') }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Nouvel équipement
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
