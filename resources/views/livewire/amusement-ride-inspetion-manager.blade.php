{{-- resources/views/livewire/amusement-ride-inspection-manager.blade.php --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-4">
            <a href="{{ $equipment ? route('equipment.show', $equipment) : route('playgrounds.show', $playground) }}"
               class="text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">
                Inspection EN 13814 - Manèges et Attractions
            </h1>
        </div>

        <div class="text-gray-600">
            @if($equipment)
                <p><strong>Équipement:</strong> {{ $equipment->reference_code }} - {{ $equipment->equipment_type }}</p>
                <p><strong>Installation:</strong> {{ $equipment->playground->name }}</p>
                @if($equipment->ride_category)
                    <p><strong>Catégorie:</strong> {{ $equipment->ride_category_label }}</p>
                @endif
            @else
                <p><strong>Installation:</strong> {{ $playground->name }}</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Contenu principal -->
        <div class="lg:col-span-3 space-y-6">
            <!-- Informations générales -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Informations générales</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Sélection équipement si pas défini -->
                    @if(!$equipment && $availableEquipment->count() > 0)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Équipement à inspecter *</label>
                            <select wire:model="equipment" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Sélectionner un équipement</option>
                                @foreach($availableEquipment as $eq)
                                    <option value="{{ $eq->id }}">{{ $eq->reference_code }} - {{ $eq->equipment_type }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type d'inspection *</label>
                        <select wire:model.live="inspection_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach($inspectionTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('inspection_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date d'inspection *</label>
                        <input type="date" wire:model.live="inspection_date"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('inspection_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom de l'inspecteur *</label>
                        <input type="text" wire:model="inspector_name"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Nom complet de l'inspecteur">
                        @error('inspector_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Qualification *</label>
                        <input type="text" wire:model="inspector_qualification"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Certificat/Agrément">
                        @error('inspector_qualification') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Organisme de contrôle</label>
                        <input type="text" wire:model="inspection_body"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Nom de l'organisme">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Heure de début</label>
                        <input type="time" wire:model="start_time"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Heure de fin</label>
                        <input type="time" wire:model="end_time"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vitesse du vent (km/h)</label>
                        <input type="number" wire:model="wind_speed" step="0.1"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Conditions météo -->
                <div class="mt-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Conditions météorologiques</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs text-gray-600">Température (°C)</label>
                            <input type="number" wire:model="weather_conditions.temperature"
                                   class="w-full rounded border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600">Humidité (%)</label>
                            <input type="number" wire:model="weather_conditions.humidity"
                                   class="w-full rounded border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600">Visibilité</label>
                            <select wire:model="weather_conditions.visibility" class="w-full rounded border-gray-300 text-sm">
                                <option value="excellent">Excellente</option>
                                <option value="good">Bonne</option>
                                <option value="reduced">Réduite</option>
                                <option value="poor">Mauvaise</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600">Précipitations</label>
                            <select wire:model="weather_conditions.precipitation" class="w-full rounded border-gray-300 text-sm">
                                <option value="none">Aucune</option>
                                <option value="light_rain">Pluie légère</option>
                                <option value="heavy_rain">Forte pluie</option>
                                <option value="snow">Neige</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contrôles structurels -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Contrôles structurels</h2>

                <div class="space-y-4">
                    @foreach($structural_checks as $key => $check)
                        <div class="border rounded p-4">
                            <div class="flex items-center justify-between mb-2">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="structural_checks.{{ $key }}.checked"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                                    <span class="font-medium text-gray-900">
                                        {{ str_replace('_', ' ', ucfirst($key)) }}
                                    </span>
                                </label>
                                @if($structural_checks[$key]['checked'])
                                    <div class="flex gap-2">
                                        <button wire:click="updateCheckResult('structural_checks', '{{ $key }}', 'pass')"
                                                class="px-3 py-1 text-xs rounded {{ $structural_checks[$key]['result'] === 'pass' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                            Conforme
                                        </button>
                                        <button wire:click="updateCheckResult('structural_checks', '{{ $key }}', 'fail')"
                                                class="px-3 py-1 text-xs rounded {{ $structural_checks[$key]['result'] === 'fail' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600' }}">
                                            Non conforme
                                        </button>
                                    </div>
                                @endif
                            </div>
                            @if($structural_checks[$key]['checked'])
                                <textarea wire:model="structural_checks.{{ $key }}.notes"
                                          rows="2"
                                          class="w-full text-sm rounded border-gray-300"
                                          placeholder="Observations..."></textarea>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Contrôles mécaniques -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Contrôles mécaniques</h2>

                <div class="space-y-4">
                    @foreach($mechanical_checks as $key => $check)
                        <div class="border rounded p-4">
                            <div class="flex items-center justify-between mb-2">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="mechanical_checks.{{ $key }}.checked"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                                    <span class="font-medium text-gray-900">
                                        {{ str_replace('_', ' ', ucfirst($key)) }}
                                    </span>
                                </label>
                                @if($mechanical_checks[$key]['checked'])
                                    <div class="flex gap-2">
                                        <button wire:click="updateCheckResult('mechanical_checks', '{{ $key }}', 'pass')"
                                                class="px-3 py-1 text-xs rounded {{ $mechanical_checks[$key]['result'] === 'pass' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                            Conforme
                                        </button>
                                        <button wire:click="updateCheckResult('mechanical_checks', '{{ $key }}', 'fail')"
                                                class="px-3 py-1 text-xs rounded {{ $mechanical_checks[$key]['result'] === 'fail' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600' }}">
                                            Non conforme
                                        </button>
                                    </div>
                                @endif
                            </div>
                            @if($mechanical_checks[$key]['checked'])
                                <textarea wire:model="mechanical_checks.{{ $key }}.notes"
                                          rows="2"
                                          class="w-full text-sm rounded border-gray-300"
                                          placeholder="Observations..."></textarea>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 pt-4 border-t">
                    <button wire:click="performSystemTest('braking_system')"
                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Test système de freinage
                    </button>
                </div>
            </div>

            <!-- Contrôles électriques -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Contrôles électriques</h2>

                <div class="space-y-4">
                    @foreach($electrical_checks as $key => $check)
                        <div class="border rounded p-4">
                            <div class="flex items-center justify-between mb-2">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="electrical_checks.{{ $key }}.checked"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                                    <span class="font-medium text-gray-900">
                                        {{ str_replace('_', ' ', ucfirst($key)) }}
                                    </span>
                                </label>
                                @if($electrical_checks[$key]['checked'])
                                    <div class="flex gap-2">
                                        <button wire:click="updateCheckResult('electrical_checks', '{{ $key }}', 'pass')"
                                                class="px-3 py-1 text-xs rounded {{ $electrical_checks[$key]['result'] === 'pass' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                            Conforme
                                        </button>
                                        <button wire:click="updateCheckResult('electrical_checks', '{{ $key }}', 'fail')"
                                                class="px-3 py-1 text-xs rounded {{ $electrical_checks[$key]['result'] === 'fail' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600' }}">
                                            Non conforme
                                        </button>
                                    </div>
                                @endif
                            </div>
                            @if($electrical_checks[$key]['checked'])
                                <textarea wire:model="electrical_checks.{{ $key }}.notes"
                                          rows="2"
                                          class="w-full text-sm rounded border-gray-300"
                                          placeholder="Observations..."></textarea>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Systèmes de sécurité -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Systèmes de sécurité</h2>

                <div class="space-y-4">
                    @foreach($safety_system_checks as $key => $check)
                        <div class="border rounded p-4">
                            <div class="flex items-center justify-between mb-2">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="safety_system_checks.{{ $key }}.checked"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                                    <span class="font-medium text-gray-900">
                                        {{ str_replace('_', ' ', ucfirst($key)) }}
                                    </span>
                                </label>
                                @if($safety_system_checks[$key]['checked'])
                                    <div class="flex gap-2">
                                        <button wire:click="updateCheckResult('safety_system_checks', '{{ $key }}', 'pass')"
                                                class="px-3 py-1 text-xs rounded {{ $safety_system_checks[$key]['result'] === 'pass' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                            Conforme
                                        </button>
                                        <button wire:click="updateCheckResult('safety_system_checks', '{{ $key }}', 'fail')"
                                                class="px-3 py-1 text-xs rounded {{ $safety_system_checks[$key]['result'] === 'fail' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600' }}">
                                            Non conforme
                                        </button>
                                    </div>
                                @endif
                            </div>
                            @if($safety_system_checks[$key]['checked'])
                                <textarea wire:model="safety_system_checks.{{ $key }}.notes"
                                          rows="2"
                                          class="w-full text-sm rounded border-gray-300"
                                          placeholder="Observations..."></textarea>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 pt-4 border-t">
                    <button wire:click="performSystemTest('emergency_stop')"
                            class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 mr-2">
                        Test arrêt d'urgence
                    </button>
                </div>
            </div>

            <!-- Systèmes de retenue -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Systèmes de retenue passagers</h2>

                <div class="space-y-4">
                    @foreach($restraint_system_checks as $key => $check)
                        <div class="border rounded p-4">
                            <div class="flex items-center justify-between mb-2">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="restraint_system_checks.{{ $key }}.checked"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                                    <span class="font-medium text-gray-900">
                                        {{ str_replace('_', ' ', ucfirst($key)) }}
                                    </span>
                                </label>
                                @if($restraint_system_checks[$key]['checked'])
                                    <div class="flex gap-2">
                                        <button wire:click="updateCheckResult('restraint_system_checks', '{{ $key }}', 'pass')"
                                                class="px-3 py-1 text-xs rounded {{ $restraint_system_checks[$key]['result'] === 'pass' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                            Conforme
                                        </button>
                                        <button wire:click="updateCheckResult('restraint_system_checks', '{{ $key }}', 'fail')"
                                                class="px-3 py-1 text-xs rounded {{ $restraint_system_checks[$key]['result'] === 'fail' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600' }}">
                                            Non conforme
                                        </button>
                                    </div>
                                @endif
                            </div>
                            @if($restraint_system_checks[$key]['checked'])
                                <textarea wire:model="restraint_system_checks.{{ $key }}.notes"
                                          rows="2"
                                          class="w-full text-sm rounded border-gray-300"
                                          placeholder="Observations..."></textarea>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 pt-4 border-t">
                    <button wire:click="performSystemTest('restraint_system')"
                            class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                        Test systèmes de retenue
                    </button>
                </div>
            </div>

            <!-- Tests de fonctionnement -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Tests de fonctionnement</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="test_run_performed"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                            <span class="font-medium text-gray-900">Test de fonctionnement effectué</span>
                        </label>
                    </div>
                </div>

                @if($test_run_performed)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cycles de test</label>
                            <input type="number" wire:model="test_cycles"
                                   class="w-full rounded border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vitesse max (km/h)</label>
                            <input type="number" wire:model="max_speed_recorded" step="0.1"
                                   class="w-full rounded border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Accélération max (m/s²)</label>
                            <input type="number" wire:model="max_acceleration_recorded" step="0.1"
                                   class="w-full rounded border-gray-300 text-sm">
                        </div>
                    </div>
                @endif

                <div class="flex gap-2">
                    <button wire:click="runFullTestSequence"
                            class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        Lancer séquence de tests complète
                    </button>
                </div>
            </div>

            <!-- Conclusions -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Conclusions de l'inspection</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Résultat global *</label>
                        <select wire:model.live="overall_result" class="w-full rounded border-gray-300">
                            @foreach($resultOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('overall_result') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date prochaine inspection</label>
                        <input type="date" wire:model="next_inspection_date"
                               class="w-full rounded border-gray-300">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observations générales</label>
                        <textarea wire:model="observations" rows="3"
                                  class="w-full rounded border-gray-300"
                                  placeholder="Observations et commentaires généraux..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Défauts constatés</label>
                        <textarea wire:model="defects_found" rows="3"
                                  class="w-full rounded border-gray-300"
                                  placeholder="Liste des défauts et non-conformités..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Actions correctives requises</label>
                        <textarea wire:model="corrective_actions" rows="3"
                                  class="w-full rounded border-gray-300"
                                  placeholder="Actions à entreprendre pour corriger les défauts..."></textarea>
                    </div>
                </div>

                <div class="flex items-center gap-4 mb-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="operation_authorized"
                               class="rounded border-gray-300 text-green-600 focus:ring-green-500 mr-2">
                        <span class="font-medium {{ $operation_authorized ? 'text-green-900' : 'text-red-900' }}">
                            Autorisation d'exploitation
                        </span>
                    </label>
                </div>

                @if(!$operation_authorized)
                    <div class="bg-red-50 border border-red-200 rounded p-4">
                        <h4 class="font-medium text-red-900 mb-2">Restrictions d'exploitation</h4>
                        <textarea wire:model="operating_restrictions" rows="2"
                                  class="w-full rounded border-red-300"
                                  placeholder="Détails des restrictions ou interdictions..."></textarea>
                    </div>
                @endif
            </div>
        </div>

        <!-- Panneau latéral -->
        <div class="space-y-6">
            <!-- Statut de conformité -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Statut de conformité</h3>

                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Contrôles effectués</span>
                        <span class="font-medium">{{ $complianceStatus['total_checks'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Conformes</span>
                        <span class="font-medium text-green-600">{{ $complianceStatus['passed_checks'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Défaillances critiques</span>
                        <span class="font-medium text-red-600">{{ $complianceStatus['critical_fails'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Taux de conformité</span>
                        <span class="font-medium">{{ $complianceStatus['compliance_rate'] }}%</span>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $complianceStatus['compliance_rate'] >= 90 ? 'bg-green-500' : ($complianceStatus['compliance_rate'] >= 70 ? 'bg-yellow-500' : 'bg-red-500') }}"
                             style="width: {{ $complianceStatus['compliance_rate'] }}%"></div>
                    </div>
                </div>

                <div class="mt-4 p-3 rounded {{ $complianceStatus['overall_compliant'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                    <div class="text-sm font-medium {{ $complianceStatus['overall_compliant'] ? 'text-green-900' : 'text-red-900' }}">
                        {{ $complianceStatus['overall_compliant'] ? 'Conforme EN 13814' : 'Non conforme EN 13814' }}
                    </div>
                </div>
            </div>

            <!-- Exigences EN 13814 -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Exigences EN 13814</h3>

                <div class="space-y-2 text-sm">
                    @foreach($en13814_requirements as $key => $requirement)
                        <div class="p-2 bg-gray-50 rounded">
                            <div class="font-medium text-gray-900">{{ str_replace('_', ' ', ucwords($key)) }}</div>
                            <div class="text-gray-600">{{ $requirement }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>

                <div class="space-y-3">
                    <button wire:click="saveInspection"
                            class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        {{ $mode === 'create' ? 'Enregistrer l\'inspection' : 'Mettre à jour l\'inspection' }}
                    </button>

                    <button wire:click="generateInspectionReport"
                            class="w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        Générer rapport PDF
                    </button>

                    @if($equipment)
                        <a href="{{ route('equipment.show', $equipment) }}"
                           class="block w-full bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-center">
                            Retour à l'équipement
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
