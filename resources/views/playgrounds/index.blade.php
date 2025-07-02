{{-- resources/views/playgrounds/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Installations')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Installations</h1>
                <p class="text-gray-600 mt-1">Gestion des aires de jeux, parcs d'attractions et fêtes foraines</p>
            </div>
            <a href="{{ route('playgrounds.create') }}"
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nouvelle installation
            </a>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white p-4 rounded-lg shadow-sm border mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type d'installation</label>
                <select name="facility_type" class="rounded-md border-gray-300">
                    <option value="">Tous les types</option>
                    <option value="playground" {{ request('facility_type') === 'playground' ? 'selected' : '' }}>Aire de jeux</option>
                    <option value="amusement_park" {{ request('facility_type') === 'amusement_park' ? 'selected' : '' }}>Parc d'attractions</option>
                    <option value="fairground" {{ request('facility_type') === 'fairground' ? 'selected' : '' }}>Fête foraine</option>
                    <option value="mixed_facility" {{ request('facility_type') === 'mixed_facility' ? 'selected' : '' }}>Installation mixte</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                <select name="status" class="rounded-md border-gray-300">
                    <option value="">Tous les statuts</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>En maintenance</option>
                    <option value="seasonal_closure" {{ request('status') === 'seasonal_closure' ? 'selected' : '' }}>Fermeture saisonnière</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Nom ou ville..." class="rounded-md border-gray-300">
            </div>

            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                Filtrer
            </button>

            @if(request()->hasAny(['facility_type', 'status', 'search']))
                <a href="{{ route('playgrounds.index') }}" class="text-gray-500 hover:text-gray-700">
                    Réinitialiser
                </a>
            @endif
        </form>
    </div>

    <!-- Liste des installations -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        @if($playgrounds->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Installation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Localisation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Équipements</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Risques</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($playgrounds as $playground)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $playground->name }}</div>
                                        @if($playground->manager_name)
                                            <div class="text-sm text-gray-500">{{ $playground->manager_name }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                                        {{ $playground->facility_type === 'playground' ? 'bg-blue-100 text-blue-800' :
                                           ($playground->facility_type === 'amusement_park' ? 'bg-purple-100 text-purple-800' :
                                           ($playground->facility_type === 'fairground' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800')) }}">
                                        {{ $playground->facility_type_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div>{{ $playground->city ?? 'Non renseigné' }}</div>
                                    @if($playground->postal_code)
                                        <div class="text-xs text-gray-500">{{ $playground->postal_code }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium">{{ $playground->equipment->count() }}</span>
                                        <span class="text-gray-500">équipements</span>
                                    </div>
                                    @if($playground->equipment->where('status', 'active')->count() < $playground->equipment->count())
                                        <div class="text-xs text-yellow-600">
                                            {{ $playground->equipment->where('status', '!=', 'active')->count() }} hors service
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $highRisks = $playground->riskEvaluations->where('risk_category', '>=', 4)->count();
                                        $totalRisks = $playground->riskEvaluations->where('is_present', true)->count();
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        @if($highRisks > 0)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                {{ $highRisks }} critiques
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ $totalRisks }} identifiés
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                                        {{ $playground->status === 'active' ? 'bg-green-100 text-green-800' :
                                           ($playground->status === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($playground->status) }}
                                    </span>
                                    @if($playground->is_license_expiring_soon)
                                        <div class="text-xs text-orange-600 mt-1">Licence expire bientôt</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('playgrounds.show', $playground) }}"
                                           class="text-blue-600 hover:text-blue-900">Voir</a>
                                        <a href="{{ route('playgrounds.edit', $playground) }}"
                                           class="text-indigo-600 hover:text-indigo-900">Modifier</a>
                                        <a href="{{ route('risk-analysis.create', $playground) }}"
                                           class="text-green-600 hover:text-green-900">Analyser</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $playgrounds->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune installation trouvée</h3>
                <p class="mt-1 text-sm text-gray-500">Commencez par créer votre première installation.</p>
                <div class="mt-6">
                    <a href="{{ route('playgrounds.create') }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Nouvelle installation
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
