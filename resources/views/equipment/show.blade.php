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
                        @switch($equipment->equipment_category)
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
                                {{ $equipment->equipment_category }}
                        @endswitch
                    </span>
                </div>
                <p class="text-gray-600">{{ $equipment->equipment_type }}</p>
                <p class="text-sm text-gray-500">{{ $equipment->playground->name }} - {{ $equipment->playground->city }}</p>
            </div>

            <div class="flex gap-2">
                @if($equipment->equipment_category === 'amusement_ride')
                    <a href="{{ route('inspections.create', ['equipment' => $equipment]) }}"
                       class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Inspection EN 13814
                    </a>
                @endif
                @if($equipment->equipment_category === 'electrical_system')
                    <a href="{{ route('electrical-tests.create', ['equipment' => $equipment]) }}"
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
                                @switch($equipment->status)
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
                                        {{ $equipment->status }}
                                @endswitch
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

                <!-- Coordonnées fabricant/fournisseur -->
                @if($equipment->manufacturer_details || $equipment->supplier_details)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h4 class="text-md font-medium text-gray-900 mb-3">Contacts</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if($equipment->manufacturer_details)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Fabricant</dt>
                                    <dd class="text-sm text-gray-900 whitespace-pre-line">{{ $equipment->manufacturer_details }}</dd>
                                </div>
                            @endif
                            @if($equipment->supplier_details)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Fournisseur</dt>
                                    <dd class="text-sm text-gray-900 whitespace-pre-line">{{ $equipment->supplier_details }}</dd>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Caractéristiques techniques -->
            @if($equipment->height || $equipment->max_passengers || $equipment->max_speed || $equipment->voltage)
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Caractéristiques techniques</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @if($equipment->height)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Hauteur</dt>
                                <dd class="text-sm text-gray-900">{{ $equipment->height }} m</dd>
                            </div>
                        @endif
                        @if($equipment->max_passengers)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Passagers max</dt>
                                <dd class="text-sm text-gray-900">{{ $equipment->max_passengers }}</dd>
                            </div>
                        @endif
                        @if($equipment->max_speed)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Vitesse max</dt>
                                <dd class="text-sm text-gray-900">{{ $equipment->max_speed }} km/h</dd>
                            </div>
                        @endif
                        @if($equipment->max_acceleration)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Accélération max</dt>
                                <dd class="text-sm text-gray-900">{{ $equipment->max_acceleration }} g</dd>
                            </div>
                        @endif
                        @if($equipment->voltage)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Tension</dt>
                                <dd class="text-sm text-gray-900">{{ $equipment->voltage }} V</dd>
                            </div>
                        @endif
                        @if($equipment->current)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Courant</dt>
                                <dd class="text-sm text-gray-900">{{ $equipment->current }} A</dd>
                            </div>
                        @endif
                        @if($equipment->protection_class)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Classe de protection</dt>
                                <dd class="text-sm text-gray-900">{{ $equipment->protection_class }}</dd>
                            </div>
                        @endif
                        @if($equipment->ip_rating)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Indice IP</dt>
                                <dd class="text-sm text-gray-900">{{ $equipment->ip_rating }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            @endif

            <!-- Certifications -->
            @if($equipment->certifications->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Certifications</h3>
                        <a href="{{ route('certifications.create', ['equipment' => $equipment]) }}"
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            + Ajouter
                        </a>
                    </div>
                    <div class="space-y-3">
                        @foreach($equipment->certifications as $cert)
                            <div class="border rounded p-3">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            @switch($cert->certification_type)
                                                @case('ce_marking')
                                                    Marquage CE
                                                    @break
                                                @case('declaration_conformity')
                                                    Déclaration de conformité
                                                    @break
                                                @case('technical_control')
                                                    Contrôle technique
                                                    @break
                                                @case('periodic_inspection')
                                                    Inspection périodique
                                                    @break
                                                @default
                                                    {{ $cert->certification_type }}
                                            @endswitch
                                        </div>
                                        @if($cert->norm_reference)
                                            <div class="text-sm text-gray-600">{{ $cert->norm_reference }}</div>
                                        @endif
                                        @if($cert->certificate_number)
                                            <div class="text-xs text-gray-500">N° {{ $cert->certificate_number }}</div>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        @php
                                            $isValid = $cert->expiry_date ? $cert->expiry_date->isFuture() : true;
                                        @endphp
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                                            {{ $isValid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $isValid ? 'Valide' : 'Expiré' }}
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

            <!-- Évaluations de risques -->
            @if($equipment->riskEvaluations->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Évaluations de risques</h3>
                        <a href="{{ route('risk-analysis.equipment', $equipment) }}"
                           class="text-yellow-600 hover:text-yellow-800 text-sm font-medium">
                            Analyser
                        </a>
                    </div>
                    @php
                        $risksByCategory = $equipment->riskEvaluations->where('is_present', true)->groupBy('risk_category');
                    @endphp
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach([1, 2, 3, 4, 5] as $category)
                            @php
                                $count = $risksByCategory->get($category, collect())->count();
                                $color = $category <= 2 ? 'green' : ($category == 3 ? 'yellow' : 'red');
                            @endphp
                            <div class="text-center">
                                <div class="text-2xl font-bold text-{{ $color }}-600">{{ $count }}</div>
                                <div class="text-xs text-gray-500">Risque {{ $category }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Inspections récentes (pour manèges) -->
            @if($equipment->equipment_category === 'amusement_ride' && $equipment->amusementRideInspections->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Inspections EN 13814</h3>
                        <a href="{{ route('inspections.create', ['equipment' => $equipment]) }}"
                           class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                            + Nouvelle inspection
                        </a>
                    </div>
                    <div class="space-y-3">
                        @foreach($equipment->amusementRideInspections->take(5) as $inspection)
                            <div class="border rounded p-3">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            @switch($inspection->inspection_type)
                                                @case('initial')
                                                    Inspection initiale
                                                    @break
                                                @case('periodic')
                                                    Inspection périodique
                                                    @break
                                                @case('exceptional')
                                                    Inspection exceptionnelle
                                                    @break
                                                @default
                                                    {{ $inspection->inspection_type }}
                                            @endswitch
                                        </div>
                                        @if($inspection->inspector_name)
                                            <div class="text-sm text-gray-600">{{ $inspection->inspector_name }}</div>
                                        @endif
                                        <div class="text-xs text-gray-500">{{ $inspection->inspection_date->format('d/m/Y') }}</div>
                                    </div>
                                    <div class="text-right">
                                        @php
                                            $resultColor = match($inspection->overall_result) {
                                                'conformity' => 'green',
                                                'minor_non_conformity' => 'yellow',
                                                'major_non_conformity' => 'red',
                                                default => 'gray'
                                            };
                                        @endphp
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-{{ $resultColor }}-100 text-{{ $resultColor }}-800">
                                            @switch($inspection->overall_result)
                                                @case('conformity')
                                                    Conforme
                                                    @break
                                                @case('minor_non_conformity')
                                                    Non-conformité mineure
                                                    @break
                                                @case('major_non_conformity')
                                                    Non-conformité majeure
                                                    @break
                                                @default
                                                    {{ $inspection->overall_result }}
                                            @endswitch
                                        </span>
                                        @if(isset($inspection->operation_authorized))
                                            <div class="text-xs {{ $inspection->operation_authorized ? 'text-green-600' : 'text-red-600' }} mt-1">
                                                {{ $inspection->operation_authorized ? '✓ Exploitation autorisée' : '✗ Exploitation interdite' }}
                                            </div>
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
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Tests électriques EN 60335</h3>
                        <a href="{{ route('electrical-tests.create', ['equipment' => $equipment]) }}"
                           class="text-green-600 hover:text-green-800 text-sm font-medium">
                            + Nouveau test
                        </a>
                    </div>
                    <div class="space-y-3">
                        @foreach($equipment->electricalTests->take(5) as $test)
                            <div class="border rounded p-3">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            @switch($test->test_type)
                                                @case('pat_test')
                                                    Test PAT
                                                    @break
                                                @case('insulation_test')
                                                    Test d'isolement
                                                    @break
                                                @case('earth_continuity')
                                                    Continuité de terre
                                                    @break
                                                @default
                                                    {{ $test->test_type }}
                                            @endswitch
                                        </div>
                                        @if($test->tester_name)
                                            <div class="text-sm text-gray-600">{{ $test->tester_name }}</div>
                                        @endif
                                        <div class="text-xs text-gray-500">{{ $test->test_date->format('d/m/Y') }}</div>
                                    </div>
                                    <div class="text-right">
                                        @php
                                            $resultColor = match($test->test_result) {
                                                'pass' => 'green',
                                                'fail' => 'red',
                                                'conditional_pass' => 'yellow',
                                                default => 'gray'
                                            };
                                        @endphp
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-{{ $resultColor }}-100 text-{{ $resultColor }}-800">
                                            @switch($test->test_result)
                                                @case('pass')
                                                    Réussi
                                                    @break
                                                @case('fail')
                                                    Échec
                                                    @break
                                                @case('conditional_pass')
                                                    Conditionnel
                                                    @break
                                                @default
                                                    {{ $test->test_result }}
                                            @endswitch
                                        </span>
                                        @if(isset($test->safe_to_use))
                                            <div class="text-xs {{ $test->safe_to_use ? 'text-green-600' : 'text-red-600' }} mt-1">
                                                {{ $test->safe_to_use ? '✓ Sûr à utiliser' : '⚠ Non sûr' }}
                                            </div>
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
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Maintenances</h3>
                        <a href="{{ route('maintenance.create', ['equipment' => $equipment]) }}"
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            + Planifier
                        </a>
                    </div>
                    <div class="space-y-3">
                        @foreach($equipment->maintenanceChecks->take(5) as $maintenance)
                            <div class="border rounded p-3">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            @switch($maintenance->check_type)
                                                @case('daily_inspection')
                                                    Inspection quotidienne
                                                    @break
                                                @case('weekly_inspection')
                                                    Inspection hebdomadaire
                                                    @break
                                                @case('monthly_inspection')
                                                    Inspection mensuelle
                                                    @break
                                                @case('annual_inspection')
                                                    Inspection annuelle
                                                    @break
                                                @case('detailed_inspection')
                                                    Inspection détaillée
                                                    @break
                                                @case('operational_inspection')
                                                    Inspection opérationnelle
                                                    @break
                                                @default
                                                    {{ $maintenance->check_type }}
                                            @endswitch
                                        </div>
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
                                        @php
                                            $statusColor = match($maintenance->status) {
                                                'completed' => 'green',
                                                'scheduled' => 'blue',
                                                'in_progress' => 'yellow',
                                                'overdue' => 'red',
                                                default => 'gray'
                                            };
                                        @endphp
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                                            @switch($maintenance->status)
                                                @case('completed')
                                                    Terminé
                                                    @break
                                                @case('scheduled')
                                                    Planifié
                                                    @break
                                                @case('in_progress')
                                                    En cours
                                                    @break
                                                @case('overdue')
                                                    En retard
                                                    @break
                                                @default
                                                    {{ $maintenance->status }}
                                            @endswitch
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
                            @php
                                $lastElectricalTest = $equipment->electricalTests->first();
                                $testDate = $lastElectricalTest ? $lastElectricalTest->test_date : null;
                                $isTestCurrent = $testDate && $testDate->addYear()->isFuture();
                            @endphp
                            <span class="text-sm {{ $isTestCurrent ? 'text-green-600' : 'text-red-600' }}">
                                {{ $testDate ? $testDate->format('d/m/Y') : 'Jamais' }}
                            </span>
                        </div>
                        @if($equipment->protection_class)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Classe de protection</span>
                                <span class="text-sm text-gray-900">{{ $equipment->protection_class }}</span>
                            </div>
                        @endif
                        @if($equipment->ip_rating)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Indice IP</span>
                                <span class="text-sm text-gray-900">{{ $equipment->ip_rating }}</span>
                            </div>
                        @endif
                    </div>
                @endif

                @if($equipment->equipment_category === 'amusement_ride')
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Dernière inspection</span>
                            @php
                                $lastInspection = $equipment->amusementRideInspections->first();
                                $inspectionDate = $lastInspection ? $lastInspection->inspection_date : null;
                                $isInspectionCurrent = $inspectionDate && $inspectionDate->addMonth()->isFuture();
                            @endphp
                            <span class="text-sm {{ $isInspectionCurrent ? 'text-green-600' : 'text-red-600' }}">
                                {{ $inspectionDate ? $inspectionDate->format('d/m/Y') : 'Jamais' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Exploitation autorisée</span>
                            @php
                                $operationAuthorized = $lastInspection && $lastInspection->operation_authorized;
                            @endphp
                            <span class="text-sm {{ $operationAuthorized ? 'text-green-600' : 'text-red-600' }}">
                                {{ $operationAuthorized ? 'Oui' : 'Non' }}
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
                        <a href="{{ route('inspections.create', ['equipment' => $equipment]) }}"
                           class="block w-full text-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                            Nouvelle inspection
                        </a>
                    @endif

                    @if($equipment->equipment_category === 'electrical_system')
                        <a href="{{ route('electrical-tests.create', ['equipment' => $equipment]) }}"
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

            <!-- Informations installation -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Installation</h3>
                <div class="space-y-2">
                    <div class="text-sm">
                        <div class="font-medium text-gray-900">{{ $equipment->playground->name }}</div>
                        <div class="text-gray-600">{{ $equipment->playground->address }}</div>
                        <div class="text-gray-600">{{ $equipment->playground->city }}</div>
                    </div>
                    <a href="{{ route('playgrounds.show', $equipment->playground) }}"
                       class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                        Voir l'installation
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
aaa
