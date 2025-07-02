<?php
// app/Livewire/ElectricalTestManager.php
namespace App\Livewire;

use App\Models\ElectricalSafetyTest;
use App\Models\Equipment;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ElectricalTestManager extends Component
{
    public ?Equipment $equipment       = null;
    public ?ElectricalSafetyTest $test = null;
    public $mode                       = 'create'; // create, edit, view

    // Informations générales du test
    #[Validate('required|in:initial_verification,routine_test,periodic_test,pat_test,insulation_test,earth_continuity,rcd_test,polarity_test,load_test')]
    public $test_type = 'periodic_test';

    #[Validate('required|string|max:255')]
    public $tester_name = '';

    #[Validate('required|string|max:255')]
    public $tester_qualification = '';

    #[Validate('required|date')]
    public $test_date = '';

    public $test_equipment_used = '';

                                          // Mesures et résultats
    public $insulation_resistance = null; // MΩ
    public $earth_resistance = null;      // Ω
    public $rcd_trip_current = null;      // mA
    public $rcd_trip_time = null;         // ms
    public $polarity_correct = null;      // boolean
    public $load_test_current = null;     // A

    // Mesures de tension détaillées
    public $voltage_measurements = [
        'l1_n'  => null,
        'l2_n'  => null,
        'l3_n'  => null,
        'l1_l2' => null,
        'l2_l3' => null,
        'l3_l1' => null,
        'n_e'   => null,
    ];

                                        // Conditions de test
    public $ambient_temperature = null; // °C
    public $relative_humidity = null;   // %
    public $test_conditions = '';

    // Tests spécialisés par type d'équipement
    public $specialized_tests = [
        'motor_insulation'    => ['tested' => false, 'result' => null, 'notes' => ''],
        'heater_elements'     => ['tested' => false, 'result' => null, 'notes' => ''],
        'control_circuits'    => ['tested' => false, 'result' => null, 'notes' => ''],
        'safety_interlocks'   => ['tested' => false, 'result' => null, 'notes' => ''],
        'emergency_stop'      => ['tested' => false, 'result' => null, 'notes' => ''],
        'overload_protection' => ['tested' => false, 'result' => null, 'notes' => ''],
    ];

    // Résultats et conclusions
    public $test_result = 'pass';
    public $observations = '';
    public $defects_found = '';
    public $recommendations = '';
    public $next_test_date = '';
    public $safe_to_use = true;

    // Standards de référence EN 60335
    public $standards = [
        'insulation_resistance' => [
            'min_value'    => 1.0, // MΩ
            'test_voltage' => 500, // V
            'standard'     => 'EN 60335-1 Clause 16',
        ],
        'earth_continuity'      => [
            'max_resistance' => 0.1, // Ω
            'test_current'   => 10,  // A
            'standard'       => 'EN 60335-1 Clause 27',
        ],
        'rcd_protection'        => [
            'max_trip_current' => 30, // mA
            'max_trip_time'    => 40, // ms
            'standard'         => 'EN 61008/EN 61009',
        ],
    ];

    public function mount(?Equipment $equipment = null, ?ElectricalSafetyTest $test = null)
    {
        if ($test) {
            $this->test      = $test;
            $this->equipment = $test->equipment;
            $this->mode      = 'edit';
            $this->loadTestData();
        } elseif ($equipment) {
            $this->equipment = $equipment;
            $this->mode      = 'create';
            $this->initializeForEquipment();
        }

        $this->test_date = now()->format('Y-m-d');
        $this->calculateNextTestDate();
    }

    protected function loadTestData()
    {
        if (! $this->test) {
            return;
        }

        $this->test_type            = $this->test->test_type;
        $this->tester_name          = $this->test->tester_name;
        $this->tester_qualification = $this->test->tester_qualification;
        $this->test_date            = $this->test->test_date->format('Y-m-d');
        $this->test_equipment_used  = $this->test->test_equipment_used ?? '';

        $this->insulation_resistance = $this->test->insulation_resistance;
        $this->earth_resistance      = $this->test->earth_resistance;
        $this->rcd_trip_current      = $this->test->rcd_trip_current;
        $this->rcd_trip_time         = $this->test->rcd_trip_time;
        $this->polarity_correct      = $this->test->polarity_correct;
        $this->load_test_current     = $this->test->load_test_current;

        $this->voltage_measurements = $this->test->voltage_measurements ?? $this->voltage_measurements;

        $this->ambient_temperature = $this->test->ambient_temperature;
        $this->relative_humidity   = $this->test->relative_humidity;
        $this->test_conditions     = $this->test->test_conditions ?? '';

        $this->test_result     = $this->test->test_result;
        $this->observations    = $this->test->observations ?? '';
        $this->defects_found   = $this->test->defects_found ?? '';
        $this->recommendations = $this->test->recommendations ?? '';
        $this->next_test_date  = $this->test->next_test_date?->format('Y-m-d') ?? '';
        $this->safe_to_use     = $this->test->safe_to_use;
    }

    protected function initializeForEquipment()
    {
        if (! $this->equipment) {
            return;
        }

        // Adapter les tests selon le type d'équipement électrique
        $equipmentType = $this->equipment->equipment_type;

        switch ($equipmentType) {
            case 'motor_drive':
                $this->specialized_tests['motor_insulation']['tested']    = true;
                $this->specialized_tests['overload_protection']['tested'] = true;
                break;
            case 'control_system':
                $this->specialized_tests['control_circuits']['tested']  = true;
                $this->specialized_tests['safety_interlocks']['tested'] = true;
                break;
            case 'safety_system':
                $this->specialized_tests['emergency_stop']['tested']    = true;
                $this->specialized_tests['safety_interlocks']['tested'] = true;
                break;
        }
    }

    public function updatedTestType()
    {
        $this->calculateNextTestDate();
    }

    public function calculateNextTestDate()
    {
        $nextDate = match ($this->test_type) {
            'routine_test' => now()->addMonths(3),
            'periodic_test' => now()->addYear(),
            'pat_test' => now()->addYear(),
            'initial_verification' => null,
            default => now()->addYear()
        };

        $this->next_test_date = $nextDate?->format('Y-m-d') ?? '';
    }

    public function performInsulationTest()
    {
                                               // Simuler un test d'isolement
        $baseResistance = 2.5;                 // MΩ valeur de base
        $variation      = rand(-20, 20) / 100; // ±20% de variation

        $this->insulation_resistance = round($baseResistance * (1 + $variation), 2);

        // Vérifier la conformité
        if ($this->insulation_resistance >= $this->standards['insulation_resistance']['min_value']) {
            $this->specialized_tests['motor_insulation']['result'] = 'pass';
        } else {
            $this->specialized_tests['motor_insulation']['result'] = 'fail';
            $this->defects_found .= "Résistance d'isolement insuffisante: {$this->insulation_resistance} MΩ < {$this->standards['insulation_resistance']['min_value']} MΩ. ";
        }

        $this->updateOverallResult();
    }

    public function performEarthContinuityTest()
    {
                                                // Simuler un test de continuité de terre
        $baseResistance = 0.05;                 // Ω valeur de base
        $variation      = rand(-50, 50) / 1000; // ±0.05Ω de variation

        $this->earth_resistance = round($baseResistance + $variation, 3);

        // Vérifier la conformité
        if ($this->earth_resistance <= $this->standards['earth_continuity']['max_resistance']) {
            $this->specialized_tests['safety_interlocks']['result'] = 'pass';
        } else {
            $this->specialized_tests['safety_interlocks']['result'] = 'fail';
            $this->defects_found .= "Résistance de terre excessive: {$this->earth_resistance} Ω > {$this->standards['earth_continuity']['max_resistance']} Ω. ";
        }

        $this->updateOverallResult();
    }

    public function performRcdTest()
    {
                                                // Simuler un test RCD (dispositif différentiel)
        $this->rcd_trip_current = rand(15, 35); // mA
        $this->rcd_trip_time    = rand(20, 50); // ms

        // Vérifier la conformité
        $rcdOk = $this->rcd_trip_current <= $this->standards['rcd_protection']['max_trip_current'] &&
        $this->rcd_trip_time <= $this->standards['rcd_protection']['max_trip_time'];

        if ($rcdOk) {
            $this->specialized_tests['overload_protection']['result'] = 'pass';
        } else {
            $this->specialized_tests['overload_protection']['result'] = 'fail';
            $this->defects_found .= "RCD hors spécifications: {$this->rcd_trip_current}mA/{$this->rcd_trip_time}ms. ";
        }

        $this->updateOverallResult();
    }

    public function performPolarityTest()
    {
                                                    // Simuler un test de polarité
        $this->polarity_correct = rand(0, 100) > 5; // 95% de chance d'être correct

        if (! $this->polarity_correct) {
            $this->defects_found .= "Polarité incorrecte détectée. ";
            $this->specialized_tests['control_circuits']['result'] = 'fail';
        } else {
            $this->specialized_tests['control_circuits']['result'] = 'pass';
        }

        $this->updateOverallResult();
    }

    public function performVoltageTest()
    {
        // Simuler des mesures de tension
        $nominalVoltage = $this->equipment?->voltage ?? 230;

        $this->voltage_measurements = [
            'l1_n'  => round($nominalVoltage + rand(-10, 10), 1),
            'l2_n'  => round($nominalVoltage + rand(-10, 10), 1),
            'l3_n'  => round($nominalVoltage + rand(-10, 10), 1),
            'l1_l2' => round($nominalVoltage * 1.732 + rand(-15, 15), 1),
            'l2_l3' => round($nominalVoltage * 1.732 + rand(-15, 15), 1),
            'l3_l1' => round($nominalVoltage * 1.732 + rand(-15, 15), 1),
            'n_e'   => round(rand(0, 5), 1),
        ];

        // Vérifier si les tensions sont dans les tolérances (±10%)
        $tolerance  = $nominalVoltage * 0.1;
        $voltagesOk = true;

        foreach (['l1_n', 'l2_n', 'l3_n'] as $measurement) {
            if (abs($this->voltage_measurements[$measurement] - $nominalVoltage) > $tolerance) {
                $voltagesOk = false;
                break;
            }
        }

        if (! $voltagesOk) {
            $this->defects_found .= "Tensions hors tolérances. ";
        }
    }

    public function performLoadTest()
    {
        $nominalCurrent          = $this->equipment?->current ?? 10;
        $this->load_test_current = round($nominalCurrent * 0.9, 2);

        // Vérifier la conformité
        if ($this->load_test_current > $nominalCurrent * 1.1) {
            $this->defects_found .= "Courant de charge excessif. ";
        }
    }

    public function updateOverallResult()
    {
        $failedTests      = 0;
        $criticalFailures = false;

        foreach ($this->specialized_tests as $test) {
            if ($test['tested'] && $test['result'] === 'fail') {
                $failedTests++;

                // Certains tests sont critiques
                if (in_array($test, ['safety_interlocks', 'emergency_stop', 'overload_protection'])) {
                    $criticalFailures = true;
                }
            }
        }

        // Vérifier les mesures critiques
        if ($this->insulation_resistance && $this->insulation_resistance < $this->standards['insulation_resistance']['min_value']) {
            $criticalFailures = true;
        }

        if ($this->earth_resistance && $this->earth_resistance > $this->standards['earth_continuity']['max_resistance']) {
            $criticalFailures = true;
        }

        if ($criticalFailures) {
            $this->test_result = 'fail';
            $this->safe_to_use = false;
        } elseif ($failedTests > 0) {
            $this->test_result = 'conditional';
            $this->safe_to_use = true;
        } else {
            $this->test_result = 'pass';
            $this->safe_to_use = true;
        }
    }

    public function runFullTestSequence()
    {
        $this->performInsulationTest();
        $this->performEarthContinuityTest();
        $this->performRcdTest();
        $this->performPolarityTest();
        $this->performVoltageTest();
        $this->performLoadTest();

        $this->observations = "Test automatisé complet effectué le " . now()->format('d/m/Y H:i');

        session()->flash('message', 'Séquence de tests automatique terminée.');
    }

    public function saveTest()
    {
        $this->validate();

        $data = [
            'equipment_id'          => $this->equipment->id,
            'test_type'             => $this->test_type,
            'tester_name'           => $this->tester_name,
            'tester_qualification'  => $this->tester_qualification,
            'test_date'             => $this->test_date,
            'test_equipment_used'   => $this->test_equipment_used,
            'insulation_resistance' => $this->insulation_resistance,
            'earth_resistance'      => $this->earth_resistance,
            'rcd_trip_current'      => $this->rcd_trip_current,
            'rcd_trip_time'         => $this->rcd_trip_time,
            'polarity_correct'      => $this->polarity_correct,
            'load_test_current'     => $this->load_test_current,
            'voltage_measurements'  => $this->voltage_measurements,
            'ambient_temperature'   => $this->ambient_temperature,
            'relative_humidity'     => $this->relative_humidity,
            'test_conditions'       => $this->test_conditions,
            'test_result'           => $this->test_result,
            'observations'          => $this->observations,
            'defects_found'         => $this->defects_found,
            'recommendations'       => $this->recommendations,
            'next_test_date'        => $this->next_test_date,
            'safe_to_use'           => $this->safe_to_use,
        ];

        if ($this->mode === 'create') {
            $test = ElectricalSafetyTest::create($data);
            session()->flash('message', 'Test électrique EN 60335 enregistré avec succès !');
        } else {
            $this->test->update($data);
            session()->flash('message', 'Test électrique EN 60335 mis à jour avec succès !');
        }

        // Mettre à jour la date du dernier test sur l'équipement
        if ($this->equipment) {
            $this->equipment->update([
                'electrical_test_date' => $this->test_date,
                'status'               => $this->safe_to_use ? 'active' : 'out_of_service',
            ]);
        }

        return redirect()->route('equipment.show', $this->equipment);
    }

    public function generateTestReport()
    {
        // Générer un rapport PDF du test
        $reportData = [
            'equipment'    => $this->equipment,
            'test_data'    => $this->test ?? (object) [
                'test_type'   => $this->test_type,
                'tester_name' => $this->tester_name,
                'test_date'   => $this->test_date,
                'test_result' => $this->test_result,
                'safe_to_use' => $this->safe_to_use,
            ],
            'measurements' => [
                'insulation_resistance' => $this->insulation_resistance,
                'earth_resistance'      => $this->earth_resistance,
                'rcd_trip_current'      => $this->rcd_trip_current,
                'rcd_trip_time'         => $this->rcd_trip_time,
            ],
            'standards'    => $this->standards,
        ];

        session()->flash('message', 'Rapport de test généré avec succès !');

        // Retourner la vue du rapport ou déclencher le téléchargement
        return redirect()->route('reports.electrical-test', ['test' => $this->test->id ?? 'preview']);
    }

    public function render()
    {
        return view('livewire.electrical-test-manager', [
            'testTypes'         => [
                'initial_verification' => 'Vérification initiale',
                'routine_test'         => 'Essai de routine',
                'periodic_test'        => 'Essai périodique',
                'pat_test'             => 'Test d\'appareils portables',
                'insulation_test'      => 'Test d\'isolement',
                'earth_continuity'     => 'Continuité de terre',
                'rcd_test'             => 'Test différentiel',
                'polarity_test'        => 'Test de polarité',
                'load_test'            => 'Test en charge',
            ],
            'testResultOptions' => [
                'pass'            => 'Réussi',
                'fail'            => 'Échec',
                'conditional'     => 'Conditionnel',
                'retest_required' => 'Nouveau test requis',
            ],
            'complianceStatus'  => $this->calculateComplianceStatus(),
        ]);
    }

    private function calculateComplianceStatus()
    {
        $status = [
            'insulation_ok'     => $this->insulation_resistance >= ($this->standards['insulation_resistance']['min_value'] ?? 1.0),
            'earth_ok'          => $this->earth_resistance <= ($this->standards['earth_continuity']['max_resistance'] ?? 0.1),
            'rcd_ok'            => $this->rcd_trip_current <= ($this->standards['rcd_protection']['max_trip_current'] ?? 30) &&
            $this->rcd_trip_time <= ($this->standards['rcd_protection']['max_trip_time'] ?? 40),
            'polarity_ok'       => $this->polarity_correct,
            'overall_compliant' => $this->test_result === 'pass' && $this->safe_to_use,
        ];

        return $status;
    }
}
