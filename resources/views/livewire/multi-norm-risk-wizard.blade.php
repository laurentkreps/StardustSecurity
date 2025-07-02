{{-- resources/views/livewire/multi-norm-risk-wizard.blade.php --}}
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
                Analyse de Risques Multi-Normes
            </h1>
        </div>

        <div class="text-gray-600">
            <p><strong>{{ $analysisScope === 'equipment' ? 'Équipement' : 'Installation' }}:</strong>
               {{ $equipment ? $equipment->reference_code . ' - ' . $equipment->equipment_type : $playground->name }}
            </p>
            @if($equipment && $playground)
                <p><strong>Installation:</strong> {{ $playground->name }}</p>
            @endif
        </div>

        <!-- Indicateur de progression -->
        <div class="mt-6">
            <div class="flex items-center justify-between">
                @for ($i = 1; $i <= $maxSteps; $i++)
                    <div class="flex items-center {{ $i < $maxSteps ? 'flex-1' : '' }}">
                        <button wire:click="goToStep({{ $i }})"
                                class="flex items-center justify-center w-10 h-10 rounded-full border-2 transition-colors
                                {{ $currentStep >= $i ? 'bg-blue-600 border-blue-600 text-white' : 'border-gray-300 text-gray-500 hover:border-gray-400' }}
                                {{ $currentStep > $i || $i == $currentStep + 1 ? 'cursor-pointer' : 'cursor-default' }}">
                            {{ $i }}
                        </button>
                        @if ($i < $maxSteps)
                            <div class="flex-1 h-1 mx-4 {{ $currentStep > $i ? 'bg-blue-600' : 'bg-gray-300' }}"></div>
                        @endif
                    </div>
                @endfor
            </div>
            <div class="flex justify-between mt-2 text-sm text-gray-600">
                <span>Informations</span>
                <span>Normes</span>
                <span>Dangers</span>
                <span>Évaluation</span>
                <span>Validation</span>
            </div>
        </div>
    </div>

    <!-- Contenu des étapes -->
    <div class="bg-white rounded-lg shadow-sm border p-6">
        {{-- Étape 1: Informations générales --}}
        @if ($currentStep === 1)
            <div class="space-y-6">
                <h2 class="text-xl font-semibold text-gray-900">Informations générales</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Évaluateur *</label>
                        <input type="text" wire:model="evaluator_name"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Nom de l'évaluateur">
                        @error('evaluator_name')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date d'évaluation *</label>
                        <input type="date" wire:model="evaluation_date"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('evaluation_date')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type d'évaluation</label>
                        <select wire:model="evaluation_type"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="initial">Évaluation initiale</option>
                            <option value="post_measures">Post-mesures correctives</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Portée de l'analyse</label>
                        <input type="text"
                               value="{{ $analysisScope === 'equipment' ? 'Équipement spécifique' : 'Installation complète' }}"
                               class="w-full rounded-md border-gray-300 bg-gray-50" readonly>
                    </div>
                </div>

                <!-- Information sur la méthode -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-medium text-blue-900 mb-2">Méthode d'évaluation Fine & Kinney</h3>
                    <p class="text-blue-800 mb-2">
                        <strong>Risque = Probabilité × Exposition × Gravité</strong>
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-blue-700">
                        <div>
                            <strong>Probabilité (P):</strong><br>
                            0.1 (impossible) à 10 (presque sûr)
                        </div>
                        <div>
                            <strong>Exposition (E):</strong><br>
                            0.5 (très rare) à 10 (permanente)
                        </div>
                        <div>
                            <strong>Gravité (G):</strong><br>
                            1 (blessure mineure) à 40 (catastrophique)
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Étape 2: Sélection des normes --}}
        @if ($currentStep === 2)
            <div class="space-y-6">
                <h2 class="text-xl font-semibold text-gray-900">Normes applicables</h2>

                <div class="space-y-4">
                    @foreach ($availableNorms as $norm => $description)
                        <div class="border rounded-lg p-4 hover:bg-gray-50">
                            <label class="flex items-start space-x-3 cursor-pointer">
                                <input type="checkbox"
                                       wire:model="selectedNorms"
                                       value="{{ $norm }}"
                                       class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">{{ $norm }}</div>
                                    <div class="text-sm text-gray-600 mt-1">{{ $description }}</div>

                                    @if($norm === 'EN 1176')
                                        <div class="text-xs text-gray-500 mt-1">
                                            Applicable aux équipements d'aires de jeux pour enfants et adolescents
                                        </div>
                                    @elseif($norm === 'EN 1177')
                                        <div class="text-xs text-gray-500 mt-1">
                                            Applicable aux revêtements de sols amortissant les chocs
                                        </div>
                                    @elseif($norm === 'EN 13814')
                                        <div class="text-xs text-gray-500 mt-1">
                                            Applicable aux manèges, attractions foraines et parcs d'attraction
                                        </div>
                                    @elseif($norm === 'EN 60335')
                                        <div class="text-xs text-gray-500 mt-1">
                                            Applicable à la sécurité électrique des équipements
                                        </div>
                                    @endif
                                </div>
                            </label>
                        </div>
                    @endforeach
                </div>

                @error('selectedNorms')
                    <div class="text-red-500 text-sm">{{ $message }}</div>
                @enderror

                @if(count($selectedNorms) > 0)
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h4 class="font-medium text-green-900">Normes sélectionnées:</h4>
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach($selectedNorms as $norm)
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">
                                    {{ $norm }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Étape 3: Sélection des dangers --}}
        @if ($currentStep === 3)
            <div class="space-y-6">
                <h2 class="text-xl font-semibold text-gray-900">Identification des dangers présents</h2>

                @if(count($selectedNorms) > 0)
                    @foreach ($selectedNorms as $norm)
                        @php
                            $normCategories = $filteredCategories->where('norm_category', $norm);
                        @endphp

                        @if($normCategories->count() > 0)
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                    {{ $norm }} - {{ $availableNorms[$norm] }}
                                </h3>

                                <div class="grid gap-3">
                                    @foreach ($normCategories as $category)
                                        <div class="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                            <label class="flex items-start space-x-3 cursor-pointer">
                                                <input type="checkbox"
                                                       wire:change="toggleDangerSelection({{ $category->id }})"
                                                       @if($riskEvaluations[$category->id]['is_present']) checked @endif
                                                       class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                <div class="flex-1">
                                                    <div class="font-medium text-gray-900">
                                                        {{ $category->code }} - {{ $category->title }}
                                                    </div>
                                                    @if($category->description)
                                                        <div class="text-sm text-gray-600 mt-1">{{ $category->description }}</div>
                                                    @endif
                                                    @if($category->typical_examples)
                                                        <div class="text-xs text-gray-500 mt-1">
                                                            <strong>Exemples:</strong> {{ $category->typical_examples }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="text-center py-8 text-gray-500">
                        <p>Veuillez d'abord sélectionner les normes applicables à l'étape précédente.</p>
                    </div>
                @endif
            </div>
        @endif

        {{-- Étape 4: Évaluation des risques --}}
        @if ($currentStep === 4)
            <div class="space-y-8">
                <h2 class="text-xl font-semibold text-gray-900">Évaluation Fine & Kinney</h2>

                @php
                    $selectedDangers = collect($riskEvaluations)->filter(fn($eval) => $eval['is_present']);
                @endphp

                @if($selectedDangers->count() > 0)
                    @foreach ($filteredCategories as $category)
                        @if ($riskEvaluations[$category->id]['is_present'])
                            <div class="border rounded-lg p-6 bg-gray-50">
                                <div class="flex justify-between items-start mb-4">
                                    <h3 class="font-medium text-lg text-gray-900">
                                        {{ $category->code }} - {{ $category->title }}
                                    </h3>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">
                                        {{ $category->norm_category }}
                                    </span>
                                </div>

                                <div class="grid gap-4">
                                    <!-- Description du risque -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Description spécifique du risque *
                                        </label>
                                        <textarea wire:model="riskEvaluations.{{ $category->id }}.risk_description"
                                                  rows="3"
                                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                  placeholder="Décrivez précisément le risque identifié..."></textarea>
                                        @error("riskEvaluations.{$category->id}.risk_description")
                                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Paramètres Fine & Kinney -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <!-- Probabilité -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Probabilité (P) *</label>
                                            <select wire:model.live="riskEvaluations.{{ $category->id }}.probability_value"
                                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                @foreach($probabilityValues as $value => $label)
                                                    <option value="{{ $value }}">{{ $value }} - {{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Exposition -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Exposition (E) *</label>
                                            <select wire:model.live="riskEvaluations.{{ $category->id }}.exposure_value"
                                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                @foreach($exposureValues as $value => $label)
                                                    <option value="{{ $value }}">{{ $value }} - {{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Gravité -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Gravité (G) *</label>
                                            <select wire:model.live="riskEvaluations.{{ $category->id }}.gravity_value"
                                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                @foreach($gravityValues as $value => $label)
                                                    <option value="{{ $value }}">{{ $value }} - {{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Calcul du risque -->
                                    @php
                                        $riskValue = $this->calculateRisk($category->id);
                                        $riskCategory = $this->getRiskCategory($riskValue);
                                        $riskColor = $this->getRiskCategoryColor($riskCategory);
                                    @endphp

                                    <div class="bg-white p-4 rounded border-l-4 border-{{ $riskColor }}-500">
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium">Niveau de risque calculé:</span>
                                            <span class="text-lg font-bold text-{{ $riskColor }}-600">
                                                {{ number_format($riskValue, 1) }}
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-600 mt-1">
                                            <strong>Catégorie {{ $riskCategory }}:</strong>
                                            {{ $this->getRiskCategoryLabel($riskCategory) }}
                                        </div>
                                        <div class="text-sm text-{{ $riskColor }}-700 mt-1 font-medium">
                                            {{ $this->getActionRequired($riskCategory) }}
                                        </div>
                                    </div>

                                    <!-- Mesures préventives -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Mesures préventives recommandées
                                            </label>
                                            <textarea wire:model="riskEvaluations.{{ $category->id }}.preventive_measures"
                                                      rows="3"
                                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                      placeholder="Mesures à mettre en place..."></textarea>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Mesures déjà implémentées
                                            </label>
                                            <textarea wire:model="riskEvaluations.{{ $category->id }}.implemented_measures"
                                                      rows="3"
                                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                      placeholder="Mesures déjà en place..."></textarea>
                                        </div>
                                    </div>

                                    <!-- Planning et suivi -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Date cible pour les mesures
                                            </label>
                                            <input type="date"
                                                   wire:model="riskEvaluations.{{ $category->id }}.target_date"
                                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Statut des mesures
                                            </label>
                                            <select wire:model="riskEvaluations.{{ $category->id }}.measure_status"
                                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="not_started">Non commencé</option>
                                                <option value="in_progress">En cours</option>
                                                <option value="completed">Terminé</option>
                                                <option value="on_hold">En attente</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="text-center py-8 text-gray-500">
                        <p>Aucun danger sélectionné. Retournez à l'étape précédente pour sélectionner les dangers présents.</p>
                    </div>
                @endif
            </div>
        @endif

        {{-- Étape 5: Résumé et validation --}}
        @if ($currentStep === 5 && $summaryStats)
            <div class="space-y-6">
                <h2 class="text-xl font-semibold text-gray-900">Résumé de l'analyse</h2>

                <!-- Statistiques globales -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">{{ $summaryStats['total_risks'] }}</div>
                        <div class="text-sm text-blue-800">Risques identifiés</div>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-red-600">{{ $summaryStats['high_risks'] }}</div>
                        <div class="text-sm text-red-800">Risques élevés</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600">{{ $summaryStats['critical_risks'] }}</div>
                        <div class="text-sm text-purple-800">Risques critiques</div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-gray-600">{{ number_format($summaryStats['avg_risk_value'], 1) }}</div>
                        <div class="text-sm text-gray-800">Risque moyen</div>
                    </div>
                </div>

                <!-- Résumé par norme -->
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="font-medium text-gray-900 mb-4">Répartition par norme</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($summaryStats['by_norm'] as $norm => $stats)
                            <div class="bg-white p-4 rounded border">
                                <div class="font-medium text-sm text-gray-600">{{ $norm }}</div>
                                <div class="text-lg font-bold">{{ $stats['count'] }} risques</div>
                                @if($stats['high_count'] > 0)
                                    <div class="text-sm text-red-600">{{ $stats['high_count'] }} élevés</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Liste des risques par niveau -->
                <div class="space-y-4">
                    <h3 class="font-medium text-gray-900">Détail des risques évalués</h3>

                    @php
                        $risksByCategory = [];
                        foreach ($riskEvaluations as $categoryId => $evaluation) {
                            if ($evaluation['is_present']) {
                                $riskValue = $this->calculateRisk($categoryId);
                                $riskCategory = $this->getRiskCategory($riskValue);
                                $category = $filteredCategories->find($categoryId);

                                $risksByCategory[] = [
                                    'category' => $category,
                                    'evaluation' => $evaluation,
                                    'risk_value' => $riskValue,
                                    'risk_category' => $riskCategory,
                                    'color' => $this->getRiskCategoryColor($riskCategory)
                                ];
                            }
                        }

                        // Trier par niveau de risque décroissant
                        usort($risksByCategory, function($a, $b) {
                            return $b['risk_category'] - $a['risk_category'];
                        });
                    @endphp

                    @if(count($risksByCategory) > 0)
                        <div class="space-y-3">
                            @foreach($risksByCategory as $risk)
                                <div class="bg-white p-4 rounded border-l-4 border-{{ $risk['color'] }}-500">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900">
                                                {{ $risk['category']->code }} - {{ $risk['category']->title }}
                                            </div>
                                            <div class="text-sm text-gray-600 mt-1">
                                                {{ $risk['evaluation']['risk_description'] }}
                                            </div>
                                            @if($risk['evaluation']['preventive_measures'])
                                                <div class="text-sm text-blue-600 mt-2">
                                                    <strong>Mesures:</strong> {{ $risk['evaluation']['preventive_measures'] }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-right ml-4">
                                            <div class="text-lg font-bold text-{{ $risk['color'] }}-600">
                                                {{ number_format($risk['risk_value'], 1) }}
                                            </div>
                                            <div class="text-sm text-{{ $risk['color'] }}-700">
                                                Catégorie {{ $risk['risk_category'] }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Validation finale -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-start space-x-3">
                        <input type="checkbox"
                               wire:model="finalValidation"
                               class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <div>
                            <label class="font-medium text-yellow-900">
                                Je confirme que cette analyse de risques est complète et exacte
                            </label>
                            <p class="text-sm text-yellow-800 mt-1">
                                Cette analyse sera sauvegardée et pourra être utilisée pour générer des rapports officiels.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Navigation -->
    <div class="flex justify-between items-center mt-8 pt-6 border-t">
        @if ($currentStep > 1)
            <button wire:click="previousStep"
                    class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Précédent
            </button>
        @else
            <div></div>
        @endif

        <div class="flex gap-2">
            @if ($currentStep == 5)
                <button wire:click="generatePreviewReport"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    Aperçu rapport
                </button>

                <button wire:click="saveAnalysis"
                        @if(!$finalValidation) disabled @endif
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Sauvegarder l'analyse
                </button>
            @elseif ($currentStep < $maxSteps)
                <button wire:click="nextStep"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                    Suivant
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            @endif
        </div>
    </div>
</div>
