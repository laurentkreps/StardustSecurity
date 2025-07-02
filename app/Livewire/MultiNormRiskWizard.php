<?php
// app/Livewire/MultiNormRiskWizard.php
namespace App\Livewire;

use App\Models\DangerCategory;
use App\Models\Equipment;
use App\Models\Playground;
use App\Models\RiskEvaluation;
use Livewire\Attributes\Validate;
use Livewire\Component;

class MultiNormRiskWizard extends Component
{
    public ?Playground $playground = null;
    public ?Equipment $equipment   = null;
    public $analysisScope          = 'playground'; // 'playground' ou 'equipment'

    // Progression du wizard
    public $currentStep = 1;
    public $maxSteps    = 5;

    // Étape 1: Informations générales
    #[Validate('required|string|max:255')]
    public $evaluator_name = '';

    #[Validate('required|date')]
    public $evaluation_date = '';

    public $evaluation_type = 'initial'; // initial, post_measures

    // Étape 2: Sélection des normes applicables
    public $selectedNorms = [];
    public $availableNorms = [
        'EN 1176'  => 'Équipements d\'aires de jeux et sols de sécurité',
        'EN 1177'  => 'Revêtements de sols d\'aires de jeux amortissant les chocs',
        'EN 13814' => 'Machines et structures pour fêtes foraines et parcs d\'attraction',
        'EN 60335' => 'Appareils électrodomestiques et analogues - Sécurité',
    ];

    // Étape 3: Sélection des catégories de dangers
    public $dangerCategories = [];
    public $categoriesByNorm = [];
    public $selectedDangers = [];

    // Étape 4: Évaluation Fine & Kinney
    public $riskEvaluations = [];

    // Étape 5: Mesures et validation
    public $finalValidation = false;

    // Configuration Fine & Kinney
    public $probabilityValues = [
        10  => 'Presque sûr - Se produit généralement',
        6   => 'Fort possible - Se produit fréquemment',
        3   => 'Inhabituel mais possible - Se produit occasionnellement',
        1   => 'Possible seulement à long terme - Se produit rarement',
        0.5 => 'Très improbable - Se produit très rarement',
        0.2 => 'Presque impossible - N\'est jamais arrivé mais concevable',
        0.1 => 'Impossible - N\'est jamais arrivé en de nombreuses années',
    ];

    public $exposureValues = [
        10  => 'Pendant toute la durée de présence sur l\'aire de jeux',
        6   => 'Équipement de jeu utilisé en permanence',
        3   => 'Équipement de jeu utilisé occasionnellement',
        2   => 'Équipement de jeu utilisé rarement',
        1   => 'Équipement de jeu utilisé très rarement',
        0.5 => 'Équipement de jeu presque jamais utilisé',
    ];

    public $gravityValues = [
        40 => 'Catastrophique - Décès multiple',
        15 => 'Très grave - Décès',
        7  => 'Grave - Blessure grave permanente',
        3  => 'Important - Blessure avec arrêt de travail',
        1  => 'Mineur - Blessure légère sans arrêt',
    ];

    public function mount(?Playground $playground = null, ?Equipment $equipment = null)
    {
        if ($equipment) {
            $this->equipment     = $equipment;
            $this->playground    = $equipment->playground;
            $this->analysisScope = 'equipment';
            $this->autoSelectNormsForEquipment();
        } elseif ($playground) {
            $this->playground    = $playground;
            $this->analysisScope = 'playground';
            $this->autoSelectNormsForPlayground();
        }

        $this->evaluation_date = now()->format('Y-m-d');
        $this->loadDangerCategories();
        $this->initializeRiskEvaluations();
    }

    protected function autoSelectNormsForEquipment()
    {
        $equipmentCategory = $this->equipment->equipment_category;

        switch ($equipmentCategory) {
            case 'playground_equipment':
                $this->selectedNorms = ['EN 1176', 'EN 1177'];
                break;
            case 'amusement_ride':
                $this->selectedNorms = ['EN 13814'];
                break;
            case 'electrical_system':
                $this->selectedNorms = ['EN 60335'];
                break;
            default:
                $this->selectedNorms = ['EN 1176'];
        }
    }

    protected function autoSelectNormsForPlayground()
    {
        $facilityType = $this->playground->facility_type;

        switch ($facilityType) {
            case 'playground':
                $this->selectedNorms = ['EN 1176', 'EN 1177'];
                break;
            case 'amusement_park':
            case 'fairground':
                $this->selectedNorms = ['EN 13814', 'EN 60335'];
                break;
            case 'mixed_facility':
                $this->selectedNorms = ['EN 1176', 'EN 1177', 'EN 13814', 'EN 60335'];
                break;
            default:
                $this->selectedNorms = ['EN 1176'];
        }
    }

    public function loadDangerCategories()
    {
        $this->dangerCategories = DangerCategory::active()->ordered()->get();

        // Grouper par norme
        $this->categoriesByNorm = $this->dangerCategories->groupBy('norm_category');
    }

    public function initializeRiskEvaluations()
    {
        foreach ($this->dangerCategories as $category) {
            $this->riskEvaluations[$category->id] = [
                'is_present'           => false,
                'risk_description'     => '',
                'probability_value'    => 1,
                'exposure_value'       => 1,
                'gravity_value'        => 1,
                'preventive_measures'  => $category->default_measures ? implode(', ', $category->default_measures) : '',
                'implemented_measures' => '',
                'target_date'          => '',
                'measure_status'       => 'not_started',
                'comments'             => '',
            ];
        }
    }

    public function nextStep()
    {
        $this->validateCurrentStep();

        if ($this->currentStep < $this->maxSteps) {
            $this->currentStep++;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function goToStep($step)
    {
        if ($step <= $this->currentStep || $step == $this->currentStep + 1) {
            if ($step > $this->currentStep) {
                $this->validateCurrentStep();
            }
            $this->currentStep = $step;
        }
    }

    protected function validateCurrentStep()
    {
        switch ($this->currentStep) {
            case 1:
                $this->validate([
                    'evaluator_name'  => 'required|string|max:255',
                    'evaluation_date' => 'required|date',
                ]);
                break;
            case 2:
                if (empty($this->selectedNorms)) {
                    $this->addError('selectedNorms', 'Veuillez sélectionner au moins une norme applicable.');
                }
                break;
            case 3:
                // Validation optionnelle pour la sélection des dangers
                break;
            case 4:
                $this->validateRiskEvaluations();
                break;
            case 5:
                // Validation finale
                break;
        }
    }

    protected function validateRiskEvaluations()
    {
        foreach ($this->riskEvaluations as $categoryId => $evaluation) {
            if ($evaluation['is_present']) {
                $rules = [
                    "riskEvaluations.{$categoryId}.risk_description"  => 'required|string|min:10',
                    "riskEvaluations.{$categoryId}.probability_value" => 'required|numeric|in:0.1,0.2,0.5,1,3,6,10',
                    "riskEvaluations.{$categoryId}.exposure_value"    => 'required|numeric|in:0.5,1,2,3,6,10',
                    "riskEvaluations.{$categoryId}.gravity_value"     => 'required|numeric|in:1,3,7,15,40',
                ];

                $this->validate($rules);
            }
        }
    }

    public function calculateRisk($categoryId)
    {
        $evaluation = $this->riskEvaluations[$categoryId];
        return $evaluation['probability_value'] * $evaluation['exposure_value'] * $evaluation['gravity_value'];
    }

    public function getRiskCategory($riskValue)
    {
        if ($riskValue > 320) {
            return 5;
        }

        if ($riskValue > 160) {
            return 4;
        }

        if ($riskValue > 70) {
            return 3;
        }

        if ($riskValue > 20) {
            return 2;
        }

        return 1;
    }

    public function getRiskCategoryLabel($category)
    {
        return config('risk_analysis.fine_kinney.risk_categories')[$category]['label'] ?? 'Non défini';
    }

    public function getRiskCategoryColor($category)
    {
        return config('risk_analysis.fine_kinney.risk_categories')[$category]['color'] ?? 'gray';
    }

    public function getActionRequired($category)
    {
        return config('risk_analysis.fine_kinney.risk_categories')[$category]['action'] ?? 'Évaluation requise';
    }

    public function toggleDangerSelection($categoryId)
    {
        $this->riskEvaluations[$categoryId]['is_present'] = ! $this->riskEvaluations[$categoryId]['is_present'];

        // Si le danger n'est plus présent, réinitialiser les valeurs
        if (! $this->riskEvaluations[$categoryId]['is_present']) {
            $this->riskEvaluations[$categoryId]['risk_description']  = '';
            $this->riskEvaluations[$categoryId]['probability_value'] = 1;
            $this->riskEvaluations[$categoryId]['exposure_value']    = 1;
            $this->riskEvaluations[$categoryId]['gravity_value']     = 1;
        }
    }

    public function updateRiskValue($categoryId, $parameter, $value)
    {
        $this->riskEvaluations[$categoryId][$parameter] = $value;
    }

    public function calculateSummaryStatistics()
    {
        $stats = [
            'total_risks'    => 0,
            'high_risks'     => 0,
            'critical_risks' => 0,
            'by_norm'        => [],
            'avg_risk_value' => 0,
        ];

        $totalRiskValue = 0;
        $riskCount      = 0;

        foreach ($this->selectedNorms as $norm) {
            $stats['by_norm'][$norm] = [
                'count'      => 0,
                'high_count' => 0,
                'avg_risk'   => 0,
            ];
        }

        foreach ($this->riskEvaluations as $categoryId => $evaluation) {
            if ($evaluation['is_present']) {
                $category     = $this->dangerCategories->find($categoryId);
                $riskValue    = $this->calculateRisk($categoryId);
                $riskCategory = $this->getRiskCategory($riskValue);
                $norm         = $category->norm_category;

                $stats['total_risks']++;
                $totalRiskValue += $riskValue;
                $riskCount++;

                if ($riskCategory >= 4) {
                    $stats['high_risks']++;
                }
                if ($riskCategory == 5) {
                    $stats['critical_risks']++;
                }

                if (in_array($norm, $this->selectedNorms)) {
                    $stats['by_norm'][$norm]['count']++;
                    if ($riskCategory >= 4) {
                        $stats['by_norm'][$norm]['high_count']++;
                    }
                }
            }
        }

        $stats['avg_risk_value'] = $riskCount > 0 ? $totalRiskValue / $riskCount : 0;

        return $stats;
    }

    public function saveAnalysis()
    {
        $this->validateCurrentStep();

        $savedCount = 0;

        foreach ($this->riskEvaluations as $categoryId => $evaluation) {
            if ($evaluation['is_present']) {
                RiskEvaluation::create([
                    'playground_id'        => $this->playground->id,
                    'equipment_id'         => $this->equipment?->id,
                    'danger_category_id'   => $categoryId,
                    'evaluation_type'      => $this->evaluation_type,
                    'is_present'           => true,
                    'risk_description'     => $evaluation['risk_description'],
                    'probability_value'    => $evaluation['probability_value'],
                    'exposure_value'       => $evaluation['exposure_value'],
                    'gravity_value'        => $evaluation['gravity_value'],
                    'preventive_measures'  => $evaluation['preventive_measures'],
                    'implemented_measures' => $evaluation['implemented_measures'],
                    'target_date'          => $evaluation['target_date'] ?: null,
                    'measure_status'       => $evaluation['measure_status'],
                    'evaluator_name'       => $this->evaluator_name,
                    'evaluation_date'      => $this->evaluation_date,
                    'comments'             => $evaluation['comments'],
                ]);
                $savedCount++;
            }
        }

        // Mettre à jour la date de dernière analyse
        $this->playground->update(['last_analysis_date' => $this->evaluation_date]);

        session()->flash('message', "Analyse de risques sauvegardée avec succès ! {$savedCount} évaluation(s) enregistrée(s).");

        if ($this->equipment) {
            return redirect()->route('equipment.show', $this->equipment);
        } else {
            return redirect()->route('playgrounds.show', $this->playground);
        }
    }

    public function generatePreviewReport()
    {
        // Logique pour générer un aperçu du rapport PDF
        session()->flash('message', 'Aperçu du rapport généré !');
    }

    public function render()
    {
        $filteredCategories = collect();

        if ($this->currentStep >= 3) {
            foreach ($this->selectedNorms as $norm) {
                $categories         = $this->categoriesByNorm->get($norm, collect());
                $filteredCategories = $filteredCategories->merge($categories);
            }
        }

        return view('livewire.multi-norm-risk-wizard', [
            'filteredCategories' => $filteredCategories,
            'summaryStats'       => $this->currentStep == 5 ? $this->calculateSummaryStatistics() : null,
        ]);
    }
}
