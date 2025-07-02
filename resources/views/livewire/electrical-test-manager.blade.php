{{-- resources/views/livewire/electrical-test-manager.blade.php --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-4">
            @if($equipment)
                <a href="{{ route('equipment.show', $equipment) }}"
                   class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
            @endif
            <h1 class="text-3xl font-bold text-gray-900">
                Test Électrique EN 60335
            </h1>
        </div>

        @if($equipment)
            <div class="text-gray-600">
                <p><strong>Équipement:</strong> {{ $equipment->reference_code }} - {{ $equipment->equipment_type }}</p>
                <p><strong>Installation:</strong> {{ $equipment->playground->name }}</p>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Contenu principal -->
        <div class="lg:col-span-3 space-y-6">
            <!-- Informations générales -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Informations générales</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type de test *</label>
                        <select wire:model.live="test_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach($testTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('test_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de test *</label>
                        <input type="date" wire:model.live="test_date"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('test_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom du testeur *</label>
                        <input type="text" wire:model="tester_name"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Nom complet du testeur">
                        @error('tester_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Qualification *</label>
                        <input type="text" wire:model="tester_qualification"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Certificat/Agrément">
                        @error('tester_qualification') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Équipement de test utilisé</label>
                        <input type="text" wire:model="test_equipment_used"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Modèle et référence">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prochaine date de test</label>
                        <input type="date" wire:model="next_test_date"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Conditions de test -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Conditions de test</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Température ambiante (°C)</label>
                        <input type="number" wire:model="ambient_temperature" step="0.1"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Humidité relative (%)</label>
                        <input type="number" wire:model="relative_humidity" step="0.1"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Conditions particulières</label>
                        <input type="text" wire:model="test_conditions"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Conditions spéciales">
                    </div>
                </div>
            </div>

            <!-- Tests électriques -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Tests électriques</h2>

                <div class="space-y-6">
                    <!-- Test d'isolement -->
                    <div class="border rounded-lg p-4">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-medium text-gray-900">Test d'isolement</h3>
                            <button wire:click="performInsulationTest"
                                    class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                Effectuer test
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Résistance d'isolement (MΩ)</label>
                                <input type="number" wire:model="insulation_resistance" step="0.01"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div class="flex items-end">
                                <div class="text-xs text-gray-600">
                                    <strong>Minimum requis:</strong> {{ $standards['insulation_resistance']['min_value'] }} MΩ<br>
                                    <strong>Tension de test:</strong> {{ $standards['insulation_resistance']['test_voltage'] }} V
                                </div>
                            </div>
                        </div>

                        @if($insulation_resistance)
                            <div class="mt-3 p-2 rounded {{ $complianceStatus['insulation_ok'] ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' }}">
                                {{ $complianceStatus['insulation_ok'] ? '✓ Conforme' : '✗ Non conforme' }}
                            </div>
                        @endif
                    </div>

                    <!-- Test de continuité de terre -->
                    <div class="border rounded-lg p-4">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-medium text-gray-900">Continuité de terre</h3>
                            <button wire:click="performEarthContinuityTest"
                                    class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                                Effectuer test
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Résistance de terre (Ω)</label>
                                <input type="number" wire:model="earth_resistance" step="0.001"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div class="flex items-end">
                                <div class="text-xs text-gray-600">
                                    <strong>Maximum autorisé:</strong> {{ $standards['earth_continuity']['max_resistance'] }} Ω<br>
                                    <strong>Courant de test:</strong> {{ $standards['earth_continuity']['test_current'] }} A
                                </div>
                            </div>
                        </div>

                        @if($earth_resistance)
                            <div class="mt-3 p-2 rounded {{ $complianceStatus['earth_ok'] ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' }}">
                                {{ $complianceStatus['earth_ok'] ? '✓ Conforme' : '✗ Non conforme' }}
                            </div>
                        @endif
                    </div>

                    <!-- Test RCD -->
                    <div class="border rounded-lg p-4">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-medium text-gray-900">Test différentiel (RCD)</h3>
                            <button wire:click="performRcdTest"
                                    class="bg-purple-600 text-white px-3 py-1 rounded text-sm hover:bg-purple-700">
                                Effectuer test
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Courant de déclenchement (mA)</label>
                                <input type="number" wire:model="rcd_trip_current" step="0.1"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Temps de déclenchement (ms)</label>
                                <input type="number" wire:model="rcd_trip_time" step="0.1"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        @if($rcd_trip_current && $rcd_trip_time)
                            <div class="mt-3 p-2 rounded {{ $complianceStatus['rcd_ok'] ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' }}">
                                {{ $complianceStatus['rcd_ok'] ? '✓ Conforme' : '✗ Non conforme' }}
                            </div>
                        @endif
                    </div>

                    <!-- Test de polarité -->
                    <div class="border rounded-lg p-4">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-medium text-gray-900">Test de polarité</h3>
                            <button wire:click="performPolarityTest"
                                    class="bg-orange-600 text-white px-3 py-1 rounded text-sm hover:bg-orange-700">
                                Effectuer test
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="polarity_correct"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                                    <span class="text-sm font-medium text-gray-900">Polarité correcte</span>
                                </label>
                            </div>
                        </div>

                        @if($polarity_correct !== null)
                            <div class="mt-3 p-2 rounded {{ $complianceStatus['polarity_ok'] ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' }}">
                                {{ $complianceStatus['polarity_ok'] ? '✓ Conforme' : '✗ Non conforme' }}
                            </div>
                        @endif
                    </div>

                    <!-- Tests de tension -->
                    <div class="border rounded-lg p-4">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-medium text-gray-900">Mesures de tension</h3>
                            <button wire:click="performVoltageTest"
                                    class="bg-yellow-600 text-white px-3 py-1 rounded text-sm hover:bg-yellow-700">
                                Mesurer tensions
                            </button>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            @foreach($voltage_measurements as $key => $value)
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">{{ strtoupper(str_replace('_', '-', $key)) }} (V)</label>
                                    <input type="number" wire:model="voltage_measurements.{{ $key }}" step="0.1"
                                           class="w-full rounded border-gray-300 text-sm">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Tests automatiques -->
                <div class="mt-6 pt-6 border-t">
                    <div class="flex gap-3">
                        <button wire:click="runFullTestSequence"
                                class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                            Lancer séquence complète
                        </button>
                        <button wire:click="performLoadTest"
                                class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                            Test en charge
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tests spécialisés -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Tests spécialisés</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($specialized_tests as $key => $test)
                        <div class="border rounded p-3">
                            <div class="flex items-center justify-between mb-2">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="specialized_tests.{{ $key }}.tested"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                                    <span class="font-medium text-sm">{{ str_replace('_', ' ', ucfirst($key)) }}</span>
                                </label>
                                @if($test['tested'])
                                    <select wire:model="specialized_tests.{{ $key }}.result" class="text-xs rounded border-gray-300">
                                        <option value="">Résultat</option>
                                        <option value="pass">Réussi</option>
                                        <option value="fail">Échec</option>
                                    </select>
                                @endif
                            </div>
                            @if($test['tested'])
                                <textarea wire:model="specialized_tests.{{ $key }}.notes"
                                          rows="2"
                                          class="w-full text-xs rounded border-gray-300"
                                          placeholder="Notes..."></textarea>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Conclusions -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Conclusions</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Résultat global</label>
                        <select wire:model.live="test_result" class="w-full rounded border-gray-300">
                            @foreach($testResultOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="safe_to_use"
                                   class="rounded border-gray-300 text-green-600 focus:ring-green-500 mr-2">
                            <span class="font-medium {{ $safe_to_use ? 'text-green-900' : 'text-red-900' }}">
                                Sûr à utiliser
                            </span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observations</label>
                        <textarea wire:model="observations" rows="3"
                                  class="w-full rounded border-gray-300"
                                  placeholder="Observations générales..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Défauts constatés</label>
                        <textarea wire:model="defects_found" rows="3"
                                  class="w-full rounded border-gray-300"
                                  placeholder="Liste des défauts..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Recommandations</label>
                        <textarea wire:model="recommendations" rows="3"
                                  class="w-full rounded border-gray-300"
                                  placeholder="Recommandations..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panneau latéral -->
        <div class="space-y-6">
            <!-- Statut de conformité -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Conformité EN 60335</h3>

                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Isolement</span>
                        <span class="text-sm {{ $complianceStatus['insulation_ok'] ? 'text-green-600' : 'text-red-600' }}">
                            {{ $complianceStatus['insulation_ok'] ? '✓' : '✗' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Continuité terre</span>
                        <span class="text-sm {{ $complianceStatus['earth_ok'] ? 'text-green-600' : 'text-red-600' }}">
                            {{ $complianceStatus['earth_ok'] ? '✓' : '✗' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">RCD</span>
                        <span class="text-sm {{ $complianceStatus['rcd_ok'] ? 'text-green-600' : 'text-red-600' }}">
                            {{ $complianceStatus['rcd_ok'] ? '✓' : '✗' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Polarité</span>
                        <span class="text-sm {{ $complianceStatus['polarity_ok'] ? 'text-green-600' : 'text-red-600' }}">
                            {{ $complianceStatus['polarity_ok'] ? '✓' : '✗' }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 p-3 rounded {{ $complianceStatus['overall_compliant'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                    <div class="text-sm font-medium {{ $complianceStatus['overall_compliant'] ? 'text-green-900' : 'text-red-900' }}">
                        {{ $complianceStatus['overall_compliant'] ? 'Conforme EN 60335' : 'Non conforme EN 60335' }}
                    </div>
                </div>
            </div>

            <!-- Standards de référence -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Standards EN 60335</h3>

                <div class="space-y-3 text-sm">
                    @foreach($standards as $test => $standard)
                        <div class="p-2 bg-gray-50 rounded">
                            <div class="font-medium text-gray-900">{{ str_replace('_', ' ', ucwords($test)) }}</div>
                            <div class="text-gray-600">{{ $standard['standard'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>

                <div class="space-y-3">
                    <button wire:click="saveTest"
                            class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        {{ $mode === 'create' ? 'Enregistrer le test' : 'Mettre à jour le test' }}
                    </button>

                    <button wire:click="generateTestReport"
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
