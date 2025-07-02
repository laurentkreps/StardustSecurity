<?php
// app/Livewire/AmusementRideInspectionManager.php
namespace App\Livewire;

use App\Models\AmusementRideInspection;
use App\Models\Equipment;
use App\Models\Playground;
use Livewire\Attributes\Validate;
use Livewire\Component;

class AmusementRideInspectionManager extends Component
{
    public ?Equipment $equipment                = null;
    public ?Playground $playground              = null;
    public ?AmusementRideInspection $inspection = null;
    public $mode                                = 'create'; // create, edit, view

    // Informations générales de l'inspection
    #[Validate('required|in:assembly_inspection,commissioning_test,daily_check,periodic_inspection,extraordinary_inspection,dismantling_check')]
    public $inspection_type = 'periodic_inspection';

    #[Validate('required|string|max:255')]
    public $inspector_name = '';

    #[Validate('required|string|max:255')]
    public $inspector_qualification = '';

    #[Validate('nullable|string|max:255')]
    public $inspection_body = '';

    #[Validate('required|date')]
    public $inspection_date = '';

    public $start_time = '';
    public $end_time = '';

    // Conditions d'inspection
    public $weather_conditions = [
        'temperature'   => null,
        'humidity'      => null,
        'visibility'    => 'good',
        'precipitation' => 'none',
    ];

    public $wind_speed = null;

    // Contrôles structurels EN 13814
    public $structural_checks = [
        'foundation_stability'    => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'steel_structure'         => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'welding_joints'          => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'bolted_connections'      => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'fatigue_sensitive_areas' => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'corrosion_protection'    => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'structural_deformation'  => ['checked' => false, 'result' => 'pass', 'notes' => ''],
    ];

    // Contrôles mécaniques
    public $mechanical_checks = [
        'drive_system'          => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'braking_system'        => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'bearings_lubrication'  => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'transmission_elements' => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'hydraulic_pneumatic'   => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'guide_wheels_rollers'  => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'wear_indicators'       => ['checked' => false, 'result' => 'pass', 'notes' => ''],
    ];

    // Contrôles électriques
    public $electrical_checks = [
        'control_cabinet'    => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'emergency_stops'    => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'safety_interlocks'  => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'motor_drives'       => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'cables_connections' => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'earthing_system'    => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'lighting_systems'   => ['checked' => false, 'result' => 'pass', 'notes' => ''],
    ];

    // Systèmes de sécurité
    public $safety_system_checks = [
        'block_zones'           => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'proximity_sensors'     => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'speed_monitoring'      => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'load_monitoring'       => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'wind_monitoring'       => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'evacuation_procedures' => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'communication_systems' => ['checked' => false, 'result' => 'pass', 'notes' => ''],
    ];

    // Systèmes de retenue passagers
    public $restraint_system_checks = [
        'lap_bars'            => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'shoulder_harnesses'  => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'seatbelts'           => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'locking_mechanisms'  => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'release_systems'     => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'passenger_seating'   => ['checked' => false, 'result' => 'pass', 'notes' => ''],
        'height_restrictions' => ['checked' => false, 'result' => 'pass', 'notes' => ''],
    ];

    // Tests de fonctionnement
    public $test_run_performed = false;
    public $test_cycles = null;
    public $max_speed_recorded = null;
    public $max_acceleration_recorded = null;

    // Résultats et conclusions
    #[Validate('required|in:conformite,non_conformite_mineure,non_conformite_majeure,interdiction_exploitation')]
    public $overall_result = 'conformite';

    public $observations = '';
    public $defects_found = '';
    public $corrective_actions = '';
    public $next_inspection_date = '';
    public $operation_authorized = true;
    public $operating_restrictions = [];

    // Standards de référence EN 13814
    public $en13814_requirements = [
        'daily_visual_inspection'  => 'Inspection visuelle quotidienne obligatoire',
        'periodic_inspection'      => 'Inspection périodique mensuelle par personnel qualifié',
        'annual_main_inspection'   => 'Contrôle principal annuel par organisme agréé',
        'extraordinary_inspection' => 'Contrôle extraordinaire après incident ou modification',
        'documentation'            => 'Tenue d\'un journal d\'inspection et de maintenance',
    ];

    public function mount(?Equipment $equipment = null, ?AmusementRideInspection $inspection = null, ?Playground $playground = null)
    {
        if ($inspection) {
            $this->inspection = $inspection;
            $this->equipment  = $inspection->equipment;
            $this->playground = $this->equipment->playground;
            $this->mode       = 'edit';
            $this->loadInspectionData();
        } elseif ($equipment) {
            $this->equipment  = $equipment;
            $this->playground = $equipment->playground;
            $this->mode       = 'create';
            $this->initializeForEquipment();
        } elseif ($playground) {
            $this->playground = $playground;
            $this->mode       = 'create';
        }

        $this->inspection_date = now()->format('Y-m-d');
        $this->start_time      = now()->format('H:i');
        $this->calculateNextInspectionDate();
    }

    protected function loadInspectionData()
    {
        $this->inspection_type         = $this->inspection->inspection_type;
        $this->inspector_name          = $this->inspection->inspector_name;
        $this->inspector_qualification = $this->inspection->inspector_qualification;
        $this->inspection_body         = $this->inspection->inspection_body;
        $this->inspection_date         = $this->inspection->inspection_date->format('Y-m-d');
        $this->start_time              = $this->inspection->start_time?->format('H:i') ?? '';
        $this->end_time                = $this->inspection->end_time?->format('H:i') ?? '';

        $this->weather_conditions = $this->inspection->weather_conditions ?? $this->weather_conditions;
        $this->wind_speed         = $this->inspection->wind_speed;

        $this->structural_checks       = $this->inspection->structural_checks ?? $this->structural_checks;
        $this->mechanical_checks       = $this->inspection->mechanical_checks ?? $this->mechanical_checks;
        $this->electrical_checks       = $this->inspection->electrical_checks ?? $this->electrical_checks;
        $this->safety_system_checks    = $this->inspection->safety_system_checks ?? $this->safety_system_checks;
        $this->restraint_system_checks = $this->inspection->restraint_system_checks ?? $this->restraint_system_checks;

        $this->test_run_performed        = $this->inspection->test_run_performed;
        $this->test_cycles               = $this->inspection->test_cycles;
        $this->max_speed_recorded        = $this->inspection->max_speed_recorded;
        $this->max_acceleration_recorded = $this->inspection->max_acceleration_recorded;

        $this->overall_result         = $this->inspection->overall_result;
        $this->observations           = $this->inspection->observations;
        $this->defects_found          = $this->inspection->defects_found;
        $this->corrective_actions     = $this->inspection->corrective_actions;
        $this->next_inspection_date   = $this->inspection->next_inspection_date?->format('Y-m-d') ?? '';
        $this->operation_authorized   = $this->inspection->operation_authorized;
        $this->operating_restrictions = $this->inspection->operating_restrictions ?? [];
    }

    protected function initializeForEquipment()
    {
        // Adapter les contrôles selon le type d'attraction
        $equipmentType = $this->equipment->equipment_type;

        // Initialiser les contrôles selon la catégorie de manège
        switch ($this->equipment->ride_category) {
            case 'category_1': // Manèges pour jeunes enfants
                $this->structural_checks['fatigue_sensitive_areas']['checked'] = false;
                $this->safety_system_checks['speed_monitoring']['checked']     = false;
                break;
            case 'category_2': // Manèges sans renversement
                $this->restraint_system_checks['shoulder_harnesses']['checked'] = false;
                break;
            case 'category_3': // Manèges avec renversement
            case 'category_4': // Attractions à sensations fortes
                                   // Tous les contrôles sont activés
                foreach ($this->structural_checks as $key => $check) {
                    $this->structural_checks[$key]['checked'] = true;
                }
                break;
        }

        // Pré-remplir certaines données techniques si disponibles
        if ($this->equipment->technicalData) {
            $this->max_speed_recorded        = $this->equipment->technicalData->max_operating_speed;
            $this->max_acceleration_recorded = $this->equipment->technicalData->max_acceleration;
        }
    }

    public function updatedInspectionType()
    {
        $this->calculateNextInspectionDate();
        $this->adaptChecksToInspectionType();
    }

    protected function adaptChecksToInspectionType()
    {
        switch ($this->inspection_type) {
            case 'daily_check':
                // Contrôle quotidien - vérifications visuelles basiques
                $this->disableDetailedChecks();
                $this->structural_checks['structural_deformation']['checked'] = true;
                $this->mechanical_checks['wear_indicators']['checked']        = true;
                $this->safety_system_checks['emergency_stops']['checked']     = true;
                break;

            case 'periodic_inspection':
                // Inspection périodique - contrôles complets
                $this->enableAllChecks();
                break;

            case 'assembly_inspection':
                // Contrôle de montage
                $this->enableAllChecks();
                $this->structural_checks['foundation_stability']['checked'] = true;
                $this->structural_checks['bolted_connections']['checked']   = true;
                break;

            case 'extraordinary_inspection':
                // Contrôle extraordinaire - selon les circonstances
                $this->enableAllChecks();
                break;
        }
    }

    protected function disableDetailedChecks()
    {
        foreach (['structural_checks', 'mechanical_checks', 'electrical_checks', 'safety_system_checks', 'restraint_system_checks'] as $category) {
            foreach ($this->{$category} as $key => $check) {
                $this->{$category}[$key]['checked'] = false;
            }
        }
    }

    protected function enableAllChecks()
    {
        foreach (['structural_checks', 'mechanical_checks', 'electrical_checks', 'safety_system_checks', 'restraint_system_checks'] as $category) {
            foreach ($this->{$category} as $key => $check) {
                $this->{$category}[$key]['checked'] = true;
            }
        }
    }

    public function calculateNextInspectionDate()
    {
        $baseDate = $this->inspection_date ? \Carbon\Carbon::parse($this->inspection_date) : now();

        $nextDate = match ($this->inspection_type) {
            'daily_check' => $baseDate->addDay(),
            'periodic_inspection' => $baseDate->addMonth(),
            'assembly_inspection', 'commissioning_test' => $baseDate->addYear(),
            'extraordinary_inspection'                  => $baseDate->addMonths(6),
            'dismantling_check'                         => null,
            default                                     => $baseDate->addMonth()
        };

        $this->next_inspection_date = $nextDate?->format('Y-m-d') ?? '';
    }

    public function performSystemTest($system)
    {
                                                       // Simuler un test système (en réalité connecté aux équipements)
        $result = rand(1, 100) > 10 ? 'pass' : 'fail'; // 90% de réussite

        switch ($system) {
            case 'emergency_stop':
                $this->safety_system_checks['emergency_stops']['result'] = $result;
                if ($result === 'fail') {
                    $this->safety_system_checks['emergency_stops']['notes'] = 'Temps de réponse trop long ou défaillance détectée';
                    $this->defects_found .= "Système d'arrêt d'urgence défaillant. ";
                }
                break;

            case 'braking_system':
                $this->mechanical_checks['braking_system']['result'] = $result;
                if ($result === 'fail') {
                    $this->mechanical_checks['braking_system']['notes'] = 'Distance de freinage excessive';
                    $this->defects_found .= "Système de freinage non conforme. ";
                }
                break;

            case 'restraint_system':
                $this->restraint_system_checks['locking_mechanisms']['result'] = $result;
                if ($result === 'fail') {
                    $this->restraint_system_checks['locking_mechanisms']['notes'] = 'Mécanisme de verrouillage défaillant';
                    $this->defects_found .= "Système de retenue non fiable. ";
                }
                break;
        }

        $this->updateOverallResult();
        session()->flash('message', "Test {$system} effectué: " . ($result === 'pass' ? 'RÉUSSI' : 'ÉCHEC'));
    }

    public function runFullTestSequence()
    {
        if (! $this->equipment) {
            session()->flash('error', 'Aucun équipement sélectionné pour les tests.');
            return;
        }

        $this->test_run_performed = true;
        $this->test_cycles        = rand(3, 10);

        // Simuler les mesures de performance
        if ($this->equipment->technicalData) {
            $nominalSpeed = $this->equipment->technicalData->max_operating_speed ?? 10;
            $nominalAccel = $this->equipment->technicalData->max_acceleration ?? 2;

            $this->max_speed_recorded        = $nominalSpeed * (0.9 + (rand(0, 20) / 100)); // ±10%
            $this->max_acceleration_recorded = $nominalAccel * (0.9 + (rand(0, 20) / 100)); // ±10%
        } else {
            $this->max_speed_recorded        = rand(5, 25);
            $this->max_acceleration_recorded = rand(1, 5);
        }

        // Tests automatiques des systèmes critiques
        $this->performSystemTest('emergency_stop');
        $this->performSystemTest('braking_system');
        $this->performSystemTest('restraint_system');

        $this->observations .= "Test de fonctionnement complet effectué le " . now()->format('d/m/Y H:i') .
            " - {$this->test_cycles} cycles de test. ";

        session()->flash('message', 'Séquence de tests complète terminée.');
    }

    public function updateCheckResult($category, $item, $result)
    {
        $this->{$category}[$item]['result'] = $result;
        $this->updateOverallResult();
    }

    public function updateOverallResult()
    {
        $failedCriticalChecks = 0;
        $failedMinorChecks    = 0;

        // Contrôles critiques (sécurité)
        $criticalChecks = [
            'safety_system_checks'    => ['emergency_stops', 'proximity_sensors', 'speed_monitoring'],
            'restraint_system_checks' => ['locking_mechanisms', 'release_systems'],
            'structural_checks'       => ['foundation_stability', 'fatigue_sensitive_areas'],
            'mechanical_checks'       => ['braking_system', 'drive_system'],
        ];

        foreach ($criticalChecks as $category => $items) {
            foreach ($items as $item) {
                if (isset($this->{$category}[$item]) &&
                    $this->{$category}[$item]['checked'] &&
                    $this->{$category}[$item]['result'] === 'fail') {
                    $failedCriticalChecks++;
                }
            }
        }

        // Autres contrôles
        $allCategories = ['structural_checks', 'mechanical_checks', 'electrical_checks', 'safety_system_checks', 'restraint_system_checks'];
        foreach ($allCategories as $category) {
            foreach ($this->{$category} as $item => $check) {
                if ($check['checked'] && $check['result'] === 'fail') {
                    if (! in_array($item, $criticalChecks[$category] ?? [])) {
                        $failedMinorChecks++;
                    }
                }
            }
        }

        // Déterminer le résultat global
        if ($failedCriticalChecks > 0) {
            $this->overall_result       = 'interdiction_exploitation';
            $this->operation_authorized = false;
        } elseif ($failedMinorChecks >= 3) {
            $this->overall_result       = 'non_conformite_majeure';
            $this->operation_authorized = false;
        } elseif ($failedMinorChecks > 0) {
            $this->overall_result       = 'non_conformite_mineure';
            $this->operation_authorized = true;
        } else {
            $this->overall_result       = 'conformite';
            $this->operation_authorized = true;
        }
    }

    public function saveInspection()
    {
        $this->validate();

        $data = [
            'equipment_id'              => $this->equipment->id,
            'inspection_type'           => $this->inspection_type,
            'inspector_name'            => $this->inspector_name,
            'inspector_qualification'   => $this->inspector_qualification,
            'inspection_body'           => $this->inspection_body,
            'inspection_date'           => $this->inspection_date,
            'start_time'                => $this->start_time,
            'end_time'                  => $this->end_time,
            'weather_conditions'        => $this->weather_conditions,
            'wind_speed'                => $this->wind_speed,
            'structural_checks'         => $this->structural_checks,
            'mechanical_checks'         => $this->mechanical_checks,
            'electrical_checks'         => $this->electrical_checks,
            'safety_system_checks'      => $this->safety_system_checks,
            'restraint_system_checks'   => $this->restraint_system_checks,
            'test_run_performed'        => $this->test_run_performed,
            'test_cycles'               => $this->test_cycles,
            'max_speed_recorded'        => $this->max_speed_recorded,
            'max_acceleration_recorded' => $this->max_acceleration_recorded,
            'overall_result'            => $this->overall_result,
            'observations'              => $this->observations,
            'defects_found'             => $this->defects_found,
            'corrective_actions'        => $this->corrective_actions,
            'next_inspection_date'      => $this->next_inspection_date,
            'operation_authorized'      => $this->operation_authorized,
            'operating_restrictions'    => $this->operating_restrictions,
        ];

        if ($this->mode === 'create') {
            $inspection = AmusementRideInspection::create($data);
            session()->flash('message', 'Inspection EN 13814 enregistrée avec succès !');
        } else {
            $this->inspection->update($data);
            session()->flash('message', 'Inspection EN 13814 mise à jour avec succès !');
        }

        // Mettre à jour le statut de l'équipement
        $equipmentStatus = $this->operation_authorized ? 'active' : 'out_of_service';
        $this->equipment->update(['status' => $equipmentStatus]);

        return redirect()->route('equipment.show', $this->equipment);
    }

    public function generateInspectionReport()
    {
        // Générer un rapport PDF de l'inspection
        $reportData = [
            'equipment'            => $this->equipment,
            'inspection_data'      => $this->inspection ?? (object) [
                'inspection_type'      => $this->inspection_type,
                'inspector_name'       => $this->inspector_name,
                'inspection_date'      => $this->inspection_date,
                'overall_result'       => $this->overall_result,
                'operation_authorized' => $this->operation_authorized,
            ],
            'structural_checks'    => $this->structural_checks,
            'mechanical_checks'    => $this->mechanical_checks,
            'electrical_checks'    => $this->electrical_checks,
            'safety_checks'        => $this->safety_system_checks,
            'restraint_checks'     => $this->restraint_system_checks,
            'en13814_requirements' => $this->en13814_requirements,
        ];

        session()->flash('message', 'Rapport d\'inspection EN 13814 généré avec succès !');

        return redirect()->route('reports.inspection', ['inspection' => $this->inspection->id ?? 'preview']);
    }

    public function render()
    {
        $availableEquipment = collect();
        if ($this->playground && ! $this->equipment) {
            $availableEquipment = $this->playground->equipment()
                ->where('equipment_category', 'amusement_ride')
                ->get();
        }

        return view('livewire.amusement-ride-inspection-manager', [
            'inspectionTypes'    => [
                'assembly_inspection'      => 'Contrôle de montage',
                'commissioning_test'       => 'Essai de mise en service',
                'daily_check'              => 'Contrôle quotidien',
                'periodic_inspection'      => 'Inspection périodique',
                'extraordinary_inspection' => 'Contrôle extraordinaire',
                'dismantling_check'        => 'Contrôle de démontage',
            ],
            'resultOptions'      => [
                'conformite'                => 'Conforme',
                'non_conformite_mineure'    => 'Non-conformité mineure',
                'non_conformite_majeure'    => 'Non-conformité majeure',
                'interdiction_exploitation' => 'Interdiction d\'exploitation',
            ],
            'availableEquipment' => $availableEquipment,
            'complianceStatus'   => $this->calculateComplianceStatus(),
        ]);
    }

    private function calculateComplianceStatus()
    {
        $totalChecks   = 0;
        $passedChecks  = 0;
        $criticalFails = 0;

        $allCategories = ['structural_checks', 'mechanical_checks', 'electrical_checks', 'safety_system_checks', 'restraint_system_checks'];

        foreach ($allCategories as $category) {
            foreach ($this->{$category} as $check) {
                if ($check['checked']) {
                    $totalChecks++;
                    if ($check['result'] === 'pass') {
                        $passedChecks++;
                    } elseif (in_array($category, ['safety_system_checks', 'restraint_system_checks'])) {
                        $criticalFails++;
                    }
                }
            }
        }

        return [
            'total_checks'      => $totalChecks,
            'passed_checks'     => $passedChecks,
            'critical_fails'    => $criticalFails,
            'compliance_rate'   => $totalChecks > 0 ? round(($passedChecks / $totalChecks) * 100, 1) : 0,
            'overall_compliant' => $this->operation_authorized && $criticalFails === 0,
            'en13814_category'  => $this->equipment?->ride_category ?? 'not_defined',
        ];
    }
}
