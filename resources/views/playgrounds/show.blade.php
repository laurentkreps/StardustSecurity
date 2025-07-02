{{-- resources/views/playgrounds/show.blade.php --}}
@extends('layouts.app')

@section('title', $playground->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- En-tête -->
    <div class="mb-8">
        <div class="flex justify-between items-start">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <a href="{{ route('playgrounds.index') }}" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $playground->name }}</h1>
                    <span class="px-3 py-1 text-sm font-medium rounded-full
                        {{ $playground->facility_type === 'playground' ? 'bg-blue-100 text-blue-800' :
                           ($playground->facility_type === 'amusement_park' ? 'bg-purple-100 text-purple-800' :
                           ($playground->facility_type === 'fairground' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800')) }}">
                        {{ $playground->facility_type_label }}
                    </span>
                </div>
                <p class="text-gray-600">{{ $playground->address }}, {{ $playground->city }}</p>

                <!-- Normes applicables -->
                <div class="flex gap-2 mt-2">
                    @foreach($playground->applicable_norms as $norm)
                        <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">{{ $norm }}</span>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('risk-analysis.create', $playground) }}"
                   class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    Analyse de risques
                </a>
                <a href="{{ route('playgrounds.edit', $playground) }}"
                   class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier
                </a>
            </div>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Équipements</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['equipment_count'] }}</p>
                    <p class="text-xs text-green-600">{{ $stats['active_equipment'] }} actifs</p>
                </div>
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Risques élevés</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['high_risk_count'] }}</p>
                    <p class="text-xs text-gray-500">Action requise</p>
                </div>
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Maintenances</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['overdue_maintenance'] }}</p>
                    <p class="text-xs text-gray-500">En retard</p>
                </div>
                <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                </svg>
            </div>
        </div>

        @if($playground->requires_operator_qualification)
            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Opérateurs</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $stats['qualified_operators'] }}</p>
                        <p class="text-xs text-gray-500">Qualifiés</p>
                    </div>
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        @endif

        <div class="bg-white p-6 rounded-lg shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Dernière analyse</p>
                    <p class="text-sm font-bold text-gray-900">
                        {{ $playground->last_analysis_date ? $playground->last_analysis_date->format('d/m/Y') : 'Jamais' }}
                    </p>
                    <p class="text-xs {{ $playground->last_analysis_date && $playground->last_analysis_date->addYear()->isFuture() ? 'text-green-600' : 'text-red-600' }}">
                        {{ $playground->last_analysis_date && $playground->last_analysis_date->addYear()->isFuture() ? 'À jour' : 'Expirée' }}
                    </p>
                </div>
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Tâches urgentes -->
        <div class="lg:col-span-2">
            @if(count($upcomingTasks) > 0)
                <div class="bg-white rounded-lg shadow-sm border mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Tâches urgentes</h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @foreach($upcomingTasks as $task)
                            <div class="p-4 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0 mt-1">
                                            <div class="w-2 h-2 rounded-full
                                                {{ $task['priority'] === 'high' ? 'bg-red-500' :
                                                   ($task['priority'] === 'medium' ? 'bg-yellow-500' : 'bg-blue-500') }}">
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $task['title'] }}</p>
                                            @if(isset($task['description']))
                                                <p class="text-xs text-gray-600">{{ $task['description'] }}</p>
                                            @endif
                                            <p class="text-xs text-gray-400 mt-1">
                                                Échéance: {{ $task['due_date']->format('d/m/Y') }}
                                                @if($task['due_date']->isPast())
                                                    <span class="text-red-600 font-medium">- En retard</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    @if(isset($task['url']))
                                        <a href="{{ $task['url'] }}"
                                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            Action →
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Liste des équipements -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Équipements</h3>
                    <a href="{{ route('equipment.create', ['playground_id' => $playground->id]) }}"
                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Ajouter un équipement →
                    </a>
                </div>

                @if($playground->equipment->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($playground->equipment->take(10) as $equipment)
                            <div class="p-4 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-gray-900">{{ $equipment->reference_code }}</span>
                                            <span class="px-2 py-1 bg-{{ $equipment->equipment_category === 'playground_equipment' ? 'blue' : ($equipment->equipment_category === 'amusement_ride' ? 'purple' : 'green') }}-100 text-{{ $equipment->equipment_category === 'playground_equipment' ? 'blue' : ($equipment->equipment_category === 'amusement_ride' ? 'purple' : 'green') }}-800 rounded text-xs">
                                                {{ $equipment->equipment_category_label }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600">{{ $equipment->equipment_type }}</p>
                                        @if($equipment->brand)
                                            <p class="text-xs text-gray-500">{{ $equipment->brand }}</p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                                            {{ $equipment->status === 'active' ? 'bg-green-100 text-green-800' :
                                               ($equipment->status === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($equipment->status) }}
                                        </span>
                                        <div class="mt-1">
                                            <a href="{{ route('equipment.show', $equipment) }}"
                                               class="text-blue-600 hover:text-blue-800 text-xs">
                                                Voir →
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($playground->equipment->count() > 10)
                        <div class="p-4 border-t border-gray-200 text-center">
                            <a href="{{ route('equipment.index', ['playground_id' => $playground->id]) }}"
                               class="text-blue-600 hover:text-blue-800 text-sm">
                                Voir tous les équipements ({{ $playground->equipment->count() }}) →
                            </a>
                        </div>
                    @endif
                @else
                    <div class="p-8 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <p>Aucun équipement enregistré</p>
                        <a href="{{ route('equipment.create', ['playground_id' => $playground->id]) }}"
                           class="mt-2 inline-block text-blue-600 hover:text-blue-800">
                            Ajouter le premier équipement →
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Panneau latéral -->
        <div class="space-y-6">
            <!-- Informations générales -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations</h3>
                <dl class="space-y-3">
                    @if($playground->manager_name)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Responsable</dt>
                            <dd class="text-sm text-gray-900">{{ $playground->manager_name }}</dd>
                            @if($playground->manager_contact)
                                <dd class="text-xs text-gray-600">{{ $playground->manager_contact }}</dd>
                            @endif
                        </div>
                    @endif

                    @if($playground->installation_date)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Installation</dt>
                            <dd class="text-sm text-gray-900">{{ $playground->installation_date->format('d/m/Y') }}</dd>
                        </div>
                    @endif

                    @if($playground->capacity)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Capacité</dt>
                            <dd class="text-sm text-gray-900">{{ $playground->capacity }} personnes</dd>
                        </div>
                    @endif

                    @if($playground->age_range)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Tranche d'âge</dt>
                            <dd class="text-sm text-gray-900">{{ $playground->age_range }}</dd>
                        </div>
                    @endif

                    @if($playground->operating_license)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Licence d'exploitation</dt>
                            <dd class="text-sm text-gray-900">{{ $playground->operating_license }}</dd>
                            @if($playground->license_expiry)
                                <dd class="text-xs {{ $playground->is_license_expiring_soon ? 'text-orange-600' : 'text-gray-600' }}">
                                    Expire le {{ $playground->license_expiry->format('d/m/Y') }}
                                </dd>
                            @endif
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Actions rapides -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions rapides</h3>
                <div class="space-y-3">
                    <a href="{{ route('risk-analysis.create', $playground) }}"
                       class="block w-full text-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Nouvelle analyse de risques
                    </a>

                    @if($playground->facility_type === 'amusement_park' || $playground->facility_type === 'fairground')
                        <a href="{{ route('inspections.create', ['playground' => $playground]) }}"
                           class="block w-full text-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                            Planifier une inspection
                        </a>
                    @endif

                    <a href="{{ route('reports.compliance', $playground) }}"
                       class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Rapport de conformité
                    </a>

                    <a href="{{ route('equipment.create', ['playground_id' => $playground->id]) }}"
                       class="block w-full text-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        Ajouter un équipement
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
