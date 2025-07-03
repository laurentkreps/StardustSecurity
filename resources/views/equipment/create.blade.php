{{-- resources/views/equipment/show.blade.php --}}
@extends('layouts.app')

@section('title', $equipment->reference_code)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- En-tête -->
    <div class="mb-8">
        <div class="flex justify-between items-start">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <a href="{{ route('equipment.index') }}" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $equipment->reference_code }}</h1>
                    <span class="px-3 py-1 text-sm font-medium rounded-full
                        {{ $equipment->equipment_category === 'playground_equipment' ? 'bg-blue-100 text-blue-800' :
                           ($equipment->equipment_category === 'amusement_ride' ? 'bg-purple-100 text-purple-800' :
                           ($equipment->equipment_category === 'electrical_system' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) }}">
                        {{ $equipment->equipment_category_label }}
                    </span>
                </div>
                <p class="text-gray-600">{{ $equipment->equipment_type }}</p>
                <p class="text-sm text-gray-500">{{ $equipment->playground->name }} - {{ $equipment->playground->city }}</p>
            </div>

            <div class="flex gap-2">
                @if($equipment->equipment_category === 'amusement_ride')
                    <a href="{{ route('inspections.create.equipment', $equipment) }}"
                       class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Inspection EN 13814
                    </a>
                @endif
                @if($equipment->equipment_category === 'electrical_system')
                    <a href="{{ route('electrical-tests.create.equipment', $equipment) }}"
                       class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Test EN 60335
                    </a>
                @endif
                <a href="{{ route('risk-analysis.equipment', $equipment) }}"
                   class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    Analyse de risques
                </a>
                <a href="{{ route('equipment.edit', $equipment) }}"
                   class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier
                </a>
            </div>
        </div>
    </div>

    <!-- Statut de conformité -->
    @if(isset($complianceStatus))
        <div class="mb-6 p-4 rounded-lg {{ $complianceStatus['overall'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
            <div class="flex items-center gap-2">
                @if($complianceStatus['overall'])
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-green-900">Équipement conforme</h3>
                @else
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-red-900">Non-conformités détectées</h3>
                @endif
            </div>
            @if(count($complianceStatus['issues']) > 0)
                <ul class="mt-2 text-sm {{ $complianceStatus['overall'] ? 'text-green-800' : 'text-red-800' }}">
                    @foreach($complianceStatus['issues'] as $issue)
                        <li>• {{ $issue }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    <!-- Tâches à venir -->
    @if(isset($upcomingActions) && count($upcomingActions) > 0)
        <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <h3 class="text-lg font-medium text-yellow-900 mb-3">Actions à prévoir</h3>
            <div class="space-y-2">
                @foreach($upcomingActions as $action)
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="font-medium text-yellow-900">{{ $action['title'] }}</div>
                            <div class="text-sm text-yellow-700">
                                Échéance: {{ $action['due_date']->format('d/m/Y') }}
                                @if($action['due_date']->isPast())
                                    <span class="text-red-600 font-medium">- En retard</span>
                                @endif
                            </div>
                        </div>
                        @if(isset($action['url']))
                            <a href="{{ $action['url'] }}"
                               class="text-yellow-600 hover:text-yellow-800 font-medium">
                                Planifier →
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Contenu principal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Informations générales -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations générales</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Type d'équipement</dt>
                        <dd class="text-sm text-gray-900">{{ $equipment->equipment_type }}</dd>
                    </div>
                    @if($equipment->brand)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Marque</dt>
                            <dd class="text-sm text-gray-900">{{ $equipment->brand }}</dd>
                        </div>
                    @endif
                    @if($equipment->purchase_date)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Date d'achat</dt>
                            <dd class="text-sm text-gray-900">{{ $equipment->purchase_date->format('d/m/Y') }}</dd>
                        </div>
                    @endif
                    @if($equipment->installation_date)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Date d'installation</dt>
                            <dd class="text-sm text-gray-900">{{ $equipment->installation_date->format('d/m/Y') }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Statut</dt>
                        <dd>
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                                {{ $equipment->status === 'active' ? 'bg-green-100 text-green-800' :
                                   ($equipment->status === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($equipment->status) }}
                            </span>
                        </dd>
                    </div>
                    @if($equipment->applicable_norms)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Normes applicables</dt>
                            <dd class="text-sm text-gray-900">
                                @foreach($equipment->applicable_norms as $norm)
                                    <span class="inline-block bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs mr-1">{{ $norm }}</span>
                                @endforeach
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Certifications -->
            @if($equipment->certifications->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Certifications</h3>
                    <div class="space-y-3">
                        @foreach($equipment->certifications as $cert)
                            <div class="border rounded p-3">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $cert->certification_type_label }}</div>
                                        <div class="text-sm text-gray-600">{{ $cert->norm_reference }}</div>
                                        @if($cert->certificate_number)
                                            <div class="text-xs text-gray-500">N° {{ $cert->certificate_number }}</div>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                                            {{ $cert->is_valid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $cert->status }}
                                        </span>
                                        @if($cert->expiry_date)
                                            <div class="text-xs text-gray-500 mt-1">
                                                Expire le {{ $cert->expiry_date->format('d/m/Y') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Inspections récentes (pour manèges) -->
            @if($equipment->equipment_category === 'amusement_ride' && $equipment->amusementRideInspections->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Inspections EN 13814</h3>
                    <div class="space-y-3">
                        @foreach($equipment->amusementRideInspections->take(5) as $inspection)
                            <div class="border rounded p-3">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $inspection->inspection_type_label }}</div>
                                        <div class="text-sm text-gray-600">{{ $inspection->inspector_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $inspection->inspection_date->format('d/m/Y') }}</div>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-{{ $inspection->result_color }}-100 text-{{ $inspection->result_color }}-800">
                                            {{ $inspection->overall_result_label }}
                                        </span>
                                        @if($inspection->operation_authorized)
                                            <div class="text-xs text-green-600 mt-1">✓ Exploitation autorisée</div>
                                        @else
                                            <div class="text-xs text-red-600 mt-1">✗ Exploitation interdite</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Tests électriques récents -->
            @if($equipment->equipment_category === 'electrical_system' && $equipment->electricalTests->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Tests électriques EN 60335</h3>
                    <div class="space-y-3">
                        @foreach($equipment->electricalTests->take(5) as $test)
                            <div class="border rounded p-3">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $test->test_type_label }}</div>
                                        <div class="text-sm text-gray-600">{{ $test->tester_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $test->test_date->format('d/m/Y') }}</div>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-{{ $test->result_color }}-100 text-{{ $test->result_color }}-800">
                                            {{ $test->test_result_label }}
                                        </span>
                                        @if($test->safe_to_use)
                                            <div class="text-xs text-green-600 mt-1">✓ Sûr à utiliser</div>
                                        @else
                                            <div class="text-xs text-red-600 mt-1">⚠ Non sûr</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Maintenances récentes -->
            @if($equipment->maintenanceChecks->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Maintenances</h3>
                    <div class="space-y-3">
                        @foreach($equipment->maintenanceChecks->take(5) as $maintenance)
                            <div class="border rounded p-3">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $maintenance->check_type_label }}</div>
                                        @if($maintenance->inspector_name)
                                            <div class="text-sm text-gray-600">{{ $maintenance->inspector_name }}</div>
                                        @endif
                                        <div class="text-xs text-gray-500">
                                            @if($maintenance->completed_date)
                                                Terminé le {{ $maintenance->completed_date->format('d/m/Y') }}
                                            @else
                                                Prévu le {{ $maintenance->scheduled_date->format('d/m/Y') }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-{{ $maintenance->status_color }}-100 text-{{ $maintenance->status_color }}-800">
                                            {{ $maintenance->status_label }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Panneau latéral -->
        <div class="space-y-6">
            <!-- Statut technique -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Statut technique</h3>

                @if($equipment->equipment_category === 'electrical_system')
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Dernier test électrique</span>
                            <span class="text-sm {{ $equipment->electrical_test_date && $equipment->electrical_test_date->addYear()->isFuture() ? 'text-green-600' : 'text-red-600' }}">
                                {{ $equipment->electrical_test_date ? $equipment->electrical_test_date->format('d/m/Y') : 'Jamais' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Classe de protection</span>
                            <span class="text-sm text-gray-900">{{ $equipment->protection_class ?? 'Non renseigné' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Indice IP</span>
                            <span class="text-sm text-gray-900">{{ $equipment->ip_rating ?? 'Non renseigné' }}</span>
                        </div>
                    </div>
                @endif

                @if($equipment->equipment_category === 'amusement_ride')
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Dernière inspection</span>
                            <span class="text-sm {{ $equipment->last_inspection_date && $equipment->last_inspection_date->addMonth()->isFuture() ? 'text-green-600' : 'text-red-600' }}">
                                {{ $equipment->last_inspection_date ? $equipment->last_inspection_date->format('d/m/Y') : 'Jamais' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Exploitation autorisée</span>
                            <span class="text-sm {{ $equipment->operation_authorized ? 'text-green-600' : 'text-red-600' }}">
                                {{ $equipment->operation_authorized ? 'Oui' : 'Non' }}
                            </span>
                        </div>
                        @if($equipment->max_passengers)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Passagers max</span>
                                <span class="text-sm text-gray-900">{{ $equipment->max_passengers }}</span>
                            </div>
                        @endif
                    </div>
                @endif

                @if($equipment->height)
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Hauteur</span>
                        <span class="text-sm text-gray-900">{{ $equipment->height }} m</span>
                    </div>
                @endif
            </div>

            <!-- Actions rapides -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('risk-analysis.equipment', $equipment) }}"
                       class="block w-full text-center px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                        Analyser les risques
                    </a>

                    @if($equipment->equipment_category === 'amusement_ride')
                        <a href="{{ route('inspections.create.equipment', $equipment) }}"
                           class="block w-full text-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                            Nouvelle inspection
                        </a>
                    @endif

                    @if($equipment->equipment_category === 'electrical_system')
                        <a href="{{ route('electrical-tests.create.equipment', $equipment) }}"
                           class="block w-full text-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Nouveau test électrique
                        </a>
                    @endif

                    <a href="{{ route('equipment.edit', $equipment) }}"
                       class="block w-full text-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Modifier
                    </a>

                    <button onclick="if(confirm('Êtes-vous sûr de vouloir supprimer cet équipement ?')) { document.getElementById('delete-form').submit(); }"
                            class="block w-full text-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Supprimer
                    </button>
                    <form id="delete-form" action="{{ route('equipment.destroy', $equipment) }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
