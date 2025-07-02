<?php
// app/Livewire/MultiNormDashboard.php
namespace App\Livewire;

use App\Models\AmusementRideInspection;
use App\Models\ElectricalSafetyTest;
use App\Models\Equipment;
use App\Models\IncidentReport;
use App\Models\MaintenanceCheck;
use App\Models\Playground;
use App\Models\RiskEvaluation;
use Livewire\Component;

class MultiNormDashboard extends Component
{
    public $selectedTimeframe  = '30'; // Nombre de jours
    public $selectedPlayground = null;
    public $selectedNorm       = 'all';

    // Statistiques principales
    public $stats          = [];
    public $normStats      = [];
    public $recentActivity = [];
    public $upcomingTasks  = [];
    public $criticalAlerts = [];

    // Graphiques et tendances
    public $riskTrendData  = [];
    public $complianceData = [];

    public function mount()
    {
        $this->calculateStatistics();
        $this->loadRecentActivity();
        $this->loadUpcomingTasks();
        $this->loadCriticalAlerts();
        $this->calculateTrends();
    }

    public function updatedSelectedTimeframe()
    {
        $this->calculateStatistics();
        $this->loadRecentActivity();
        $this->calculateTrends();
    }

    public function updatedSelectedPlayground()
    {
        $this->calculateStatistics();
        $this->loadRecentActivity();
        $this->loadUpcomingTasks();
    }

    public function updatedSelectedNorm()
    {
        $this->calculateStatistics();
        $this->loadRecentActivity();
    }

    protected function calculateStatistics()
    {
        $query = Playground::with(['equipment', 'riskEvaluations']);

        if ($this->selectedPlayground) {
            $query->where('id', $this->selectedPlayground);
        }

        $playgrounds = $query->get();

        // Statistiques globales
        $this->stats = [
            'total_playgrounds'   => $playgrounds->count(),
            'active_playgrounds'  => $playgrounds->where('status', 'active')->count(),
            'total_equipment'     => $playgrounds->sum(fn($p) => $p->equipment->count()),
            'active_equipment'    => $playgrounds->sum(fn($p) => $p->equipment->where('status', 'active')->count()),
            'total_risks'         => $this->getTotalRisks(),
            'high_risks'          => $this->getHighRisks(),
            'critical_risks'      => $this->getCriticalRisks(),
            'overdue_maintenance' => $this->getOverdueMaintenance(),
            'recent_incidents'    => $this->getRecentIncidents(),
            'compliance_rate'     => $this->calculateGlobalComplianceRate($playgrounds),
        ];

        // Statistiques par norme
        $this->normStats = [
            'EN 1176'  => $this->calculateNormStatistics('EN 1176'),
            'EN 1177'  => $this->calculateNormStatistics('EN 1177'),
            'EN 13814' => $this->calculateNormStatistics('EN 13814'),
            'EN 60335' => $this->calculateNormStatistics('EN 60335'),
        ];
    }

    protected function getTotalRisks()
    {
        $query = RiskEvaluation::where('is_present', true);

        if ($this->selectedPlayground) {
            $query->where('playground_id', $this->selectedPlayground);
        }

        if ($this->selectedNorm !== 'all') {
            $query->whereHas('dangerCategory', function ($q) {
                $q->where('regulation_reference', 'like', "%{$this->selectedNorm}%");
            });
        }

        return $query->count();
    }

    protected function getHighRisks()
    {
        $query = RiskEvaluation::where('is_present', true)->where('risk_category', '>=', 4);

        if ($this->selectedPlayground) {
            $query->where('playground_id', $this->selectedPlayground);
        }

        if ($this->selectedNorm !== 'all') {
            $query->whereHas('dangerCategory', function ($q) {
                $q->where('regulation_reference', 'like', "%{$this->selectedNorm}%");
            });
        }

        return $query->count();
    }

    protected function getCriticalRisks()
    {
        $query = RiskEvaluation::where('is_present', true)->where('risk_category', 5);

        if ($this->selectedPlayground) {
            $query->where('playground_id', $this->selectedPlayground);
        }

        return $query->count();
    }

    protected function getOverdueMaintenance()
    {
        $query = MaintenanceCheck::where('status', 'overdue');

        if ($this->selectedPlayground) {
            $query->where('playground_id', $this->selectedPlayground);
        }

        return $query->count();
    }

    protected function getRecentIncidents()
    {
        $query = IncidentReport::where('incident_date', '>=', now()->subDays($this->selectedTimeframe));

        if ($this->selectedPlayground) {
            $query->where('playground_id', $this->selectedPlayground);
        }

        return $query->count();
    }

    protected function calculateGlobalComplianceRate($playgrounds)
    {
        if ($playgrounds->isEmpty()) {
            return 0;
        }

        $totalScore = 0;
        $count      = 0;

        foreach ($playgrounds as $playground) {
            $score = $this->calculatePlaygroundComplianceRate($playground);
            $totalScore += $score;
            $count++;
        }

        return $count > 0 ? round($totalScore / $count, 1) : 0;
    }

    protected function calculatePlaygroundComplianceRate($playground)
    {
        $criteria = [
            'recent_analysis'        => $playground->last_analysis_date &&
            $playground->last_analysis_date->addYear()->isFuture(),
            'no_high_risks'          => $playground->riskEvaluations()
                ->where('is_present', true)
                ->where('risk_category', '>=', 4)
                ->count() === 0,
            'no_overdue_maintenance' => $playground->maintenanceChecks()
                ->where('status', 'overdue')
                ->count() === 0,
            'valid_license'          => ! $playground->license_expiry ||
            $playground->license_expiry->isFuture(),
            'active_equipment'       => $playground->equipment->where('status', 'active')->count() > 0,
        ];

        $metCriteria = array_sum($criteria);
        return round(($metCriteria / count($criteria)) * 100, 1);
    }

    protected function calculateNormStatistics($norm)
    {
        $stats = [
            'applicable_facilities' => 0,
            'compliant_facilities'  => 0,
            'total_equipment'       => 0,
            'high_risk_count'       => 0,
            'recent_inspections'    => 0,
            'compliance_rate'       => 0,
        ];

        $playgrounds = Playground::with(['equipment', 'riskEvaluations'])->get();

        foreach ($playgrounds as $playground) {
            if ($this->isNormApplicable($playground, $norm)) {
                $stats['applicable_facilities']++;

                // Équipements concernés par cette norme
                $relevantEquipment = $playground->equipment->filter(function ($equipment) use ($norm) {
                    return $this->isEquipmentSubjectToNorm($equipment, $norm);
                });

                $stats['total_equipment'] += $relevantEquipment->count();

                // Risques élevés pour cette norme
                $highRisks = $playground->riskEvaluations
                    ->where('is_present', true)
                    ->where('risk_category', '>=', 4)
                    ->filter(function ($risk) use ($norm) {
                        return str_contains($risk->dangerCategory->regulation_reference, $norm);
                    });

                $stats['high_risk_count'] += $highRisks->count();

                // Inspections récentes pour cette norme
                $stats['recent_inspections'] += $this->getRecentInspectionsForNorm($playground, $norm);

                // Conformité pour cette norme
                if ($this->isFacilityCompliantForNorm($playground, $norm)) {
                    $stats['compliant_facilities']++;
                }
            }
        }

        $stats['compliance_rate'] = $stats['applicable_facilities'] > 0
        ? round(($stats['compliant_facilities'] / $stats['applicable_facilities']) * 100, 1)
        : 0;

        return $stats;
    }

    protected function isNormApplicable($playground, $norm)
    {
        $facilityType = $playground->facility_type;

        return match ($norm) {
            'EN 1176', 'EN 1177' => in_array($facilityType, ['playground', 'mixed_facility']),
            'EN 13814'           => in_array($facilityType, ['amusement_park', 'fairground', 'mixed_facility']),
            'EN 60335'           => $playground->equipment()->where('equipment_category', 'electrical_system')->exists(),
            default              => false
        };
    }

    protected function isEquipmentSubjectToNorm($equipment, $norm)
    {
        return match ($norm) {
            'EN 1176', 'EN 1177' => $equipment->equipment_category === 'playground_equipment',
            'EN 13814'           => $equipment->equipment_category === 'amusement_ride',
            'EN 60335'           => $equipment->equipment_category === 'electrical_system',
            default              => false
        };
    }

    protected function getRecentInspectionsForNorm($playground, $norm)
    {
        $count     = 0;
        $timeframe = now()->subDays($this->selectedTimeframe);

        switch ($norm) {
            case 'EN 13814':
                $count = AmusementRideInspection::whereHas('equipment', function ($q) use ($playground) {
                    $q->where('playground_id', $playground->id);
                })->where('inspection_date', '>=', $timeframe)->count();
                break;
            case 'EN 60335':
                $count = ElectricalSafetyTest::whereHas('equipment', function ($q) use ($playground) {
                    $q->where('playground_id', $playground->id);
                })->where('test_date', '>=', $timeframe)->count();
                break;
        }

        return $count;
    }

    protected function isFacilityCompliantForNorm($playground, $norm)
    {
        switch ($norm) {
            case 'EN 1176':
                return $playground->riskEvaluations()
                    ->whereHas('dangerCategory', function ($q) {
                        $q->where('regulation_reference', 'like', '%EN 1176%');
                    })
                    ->where('is_present', true)
                    ->where('risk_category', '>=', 4)
                    ->count() === 0;

            case 'EN 13814':
                $rides = $playground->equipment()->where('equipment_category', 'amusement_ride')->get();
                foreach ($rides as $ride) {
                    $recentInspection = $ride->amusementRideInspections()
                        ->where('inspection_date', '>=', now()->subMonth())
                        ->where('operation_authorized', true)
                        ->exists();
                    if (! $recentInspection) {
                        return false;
                    }
                }
                return true;

            case 'EN 60335':
                $electricalEquipment = $playground->equipment()->where('equipment_category', 'electrical_system')->get();
                foreach ($electricalEquipment as $equipment) {
                    $recentTest = $equipment->electricalTests()
                        ->where('test_date', '>=', now()->subYear())
                        ->where('safe_to_use', true)
                        ->exists();
                    if (! $recentTest) {
                        return false;
                    }
                }
                return true;

            default:
                return true;
        }
    }

    protected function loadRecentActivity()
    {
        $this->recentActivity = [];

        // Analyses de risques récentes
        $recentAnalyses = RiskEvaluation::with(['playground', 'equipment', 'dangerCategory'])
            ->where('created_at', '>=', now()->subDays($this->selectedTimeframe))
            ->when($this->selectedPlayground, fn($q) => $q->where('playground_id', $this->selectedPlayground))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        foreach ($recentAnalyses as $analysis) {
            $this->recentActivity[] = [
                'type'        => 'risk_analysis',
                'title'       => 'Analyse de risque - ' . $analysis->dangerCategory->code,
                'description' => $analysis->playground->name .
                ($analysis->equipment ? ' - ' . $analysis->equipment->reference_code : ''),
                'date'        => $analysis->created_at,
                'severity'    => $analysis->risk_category >= 4 ? 'high' : 'normal',
                'url'         => route('playgrounds.show', $analysis->playground),
            ];
        }

        // Inspections récentes
        $recentInspections = AmusementRideInspection::with(['equipment.playground'])
            ->where('inspection_date', '>=', now()->subDays($this->selectedTimeframe))
            ->when($this->selectedPlayground, function ($q) {
                $q->whereHas('equipment', fn($subQ) => $subQ->where('playground_id', $this->selectedPlayground));
            })
            ->orderBy('inspection_date', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentInspections as $inspection) {
            $this->recentActivity[] = [
                'type'        => 'inspection',
                'title'       => 'Inspection EN 13814 - ' . $inspection->equipment->reference_code,
                'description' => $inspection->equipment->playground->name . ' - ' . $inspection->inspection_type_label,
                'date'        => $inspection->inspection_date,
                'severity'    => ! $inspection->operation_authorized ? 'high' : 'normal',
                'url'         => route('equipment.show', $inspection->equipment),
            ];
        }

        // Tests électriques récents
        $recentTests = ElectricalSafetyTest::with(['equipment.playground'])
            ->where('test_date', '>=', now()->subDays($this->selectedTimeframe))
            ->when($this->selectedPlayground, function ($q) {
                $q->whereHas('equipment', fn($subQ) => $subQ->where('playground_id', $this->selectedPlayground));
            })
            ->orderBy('test_date', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentTests as $test) {
            $this->recentActivity[] = [
                'type'        => 'electrical_test',
                'title'       => 'Test électrique EN 60335 - ' . $test->equipment->reference_code,
                'description' => $test->equipment->playground->name . ' - ' . $test->test_type_label,
                'date'        => $test->test_date,
                'severity'    => ! $test->safe_to_use ? 'high' : 'normal',
                'url'         => route('equipment.show', $test->equipment),
            ];
        }

        // Trier par date décroissante
        usort($this->recentActivity, function ($a, $b) {
            return $b['date']->timestamp - $a['date']->timestamp;
        });

        $this->recentActivity = array_slice($this->recentActivity, 0, 15);
    }

    protected function loadUpcomingTasks()
    {
        $this->upcomingTasks = [];

        // Maintenances programmées
        $upcomingMaintenance = MaintenanceCheck::with(['playground', 'equipment'])
            ->where('status', 'scheduled')
            ->where('scheduled_date', '<=', now()->addDays(30))
            ->when($this->selectedPlayground, fn($q) => $q->where('playground_id', $this->selectedPlayground))
            ->orderBy('scheduled_date')
            ->limit(10)
            ->get();

        foreach ($upcomingMaintenance as $maintenance) {
            $this->upcomingTasks[] = [
                'type'        => 'maintenance',
                'title'       => 'Maintenance ' . $maintenance->check_type,
                'description' => $maintenance->playground->name .
                ($maintenance->equipment ? ' - ' . $maintenance->equipment->reference_code : ''),
                'due_date'    => $maintenance->scheduled_date,
                'priority'    => $maintenance->scheduled_date->isPast() ? 'high' : 'normal',
                'url'         => route('playgrounds.show', $maintenance->playground),
            ];
        }

        // Analyses de risques à renouveler
        $playgroundsNeedingAnalysis = Playground::where(function ($q) {
            $q->whereNull('last_analysis_date')
                ->orWhere('last_analysis_date', '<', now()->subYear());
        })
            ->when($this->selectedPlayground, fn($q) => $q->where('id', $this->selectedPlayground))
            ->limit(5)
            ->get();

        foreach ($playgroundsNeedingAnalysis as $playground) {
            $this->upcomingTasks[] = [
                'type'        => 'risk_analysis',
                'title'       => 'Analyse de risques à renouveler',
                'description' => $playground->name,
                'due_date'    => $playground->last_analysis_date?->addYear() ?? now(),
                'priority'    => 'medium',
                'url'         => route('risk-analysis.create', $playground),
            ];
        }

        // Tests électriques dus
        $equipmentNeedingTests = Equipment::where('equipment_category', 'electrical_system')
            ->where(function ($q) {
                $q->whereNull('electrical_test_date')
                    ->orWhere('electrical_test_date', '<', now()->subYear());
            })
            ->with('playground')
            ->when($this->selectedPlayground, fn($q) => $q->where('playground_id', $this->selectedPlayground))
            ->limit(5)
            ->get();

        foreach ($equipmentNeedingTests as $equipment) {
            $this->upcomingTasks[] = [
                'type'        => 'electrical_test',
                'title'       => 'Test électrique EN 60335 requis',
                'description' => $equipment->playground->name . ' - ' . $equipment->reference_code,
                'due_date'    => $equipment->electrical_test_date?->addYear() ?? now(),
                'priority'    => 'medium',
                'url'         => route('electrical-tests.create', ['equipment' => $equipment]),
            ];
        }

        // Trier par priorité et date
        usort($this->upcomingTasks, function ($a, $b) {
            $priorities   = ['high' => 3, 'medium' => 2, 'normal' => 1];
            $priorityDiff = $priorities[$b['priority']] - $priorities[$a['priority']];

            if ($priorityDiff === 0) {
                return $a['due_date']->timestamp - $b['due_date']->timestamp;
            }

            return $priorityDiff;
        });

        $this->upcomingTasks = array_slice($this->upcomingTasks, 0, 10);
    }

    protected function loadCriticalAlerts()
    {
        $this->criticalAlerts = [];

        // Risques critiques récents
        $criticalRisks = RiskEvaluation::with(['playground', 'equipment', 'dangerCategory'])
            ->where('is_present', true)
            ->where('risk_category', 5)
            ->where('created_at', '>=', now()->subDays(7))
            ->when($this->selectedPlayground, fn($q) => $q->where('playground_id', $this->selectedPlayground))
            ->get();

        foreach ($criticalRisks as $risk) {
            $this->criticalAlerts[] = [
                'type'        => 'critical_risk',
                'title'       => 'Risque critique identifié',
                'description' => $risk->dangerCategory->title . ' - ' . $risk->playground->name,
                'date'        => $risk->created_at,
                'action_url'  => route('playgrounds.show', $risk->playground),
            ];
        }

        // Équipements non autorisés à l'exploitation
        $unauthorizedEquipment = Equipment::whereHas('amusementRideInspections', function ($q) {
            $q->where('operation_authorized', false)
                ->where('inspection_date', '>=', now()->subMonth());
        })
            ->with('playground')
            ->when($this->selectedPlayground, fn($q) => $q->where('playground_id', $this->selectedPlayground))
            ->get();

        foreach ($unauthorizedEquipment as $equipment) {
            $this->criticalAlerts[] = [
                'type'        => 'unauthorized_equipment',
                'title'       => 'Équipement non autorisé à l\'exploitation',
                'description' => $equipment->reference_code . ' - ' . $equipment->playground->name,
                'date'        => now(),
                'action_url'  => route('equipment.show', $equipment),
            ];
        }

        // Équipements électriques non sûrs
        $unsafeElectricalEquipment = Equipment::whereHas('electricalTests', function ($q) {
            $q->where('safe_to_use', false)
                ->where('test_date', '>=', now()->subMonth());
        })
            ->with('playground')
            ->when($this->selectedPlayground, fn($q) => $q->where('playground_id', $this->selectedPlayground))
            ->get();

        foreach ($unsafeElectricalEquipment as $equipment) {
            $this->criticalAlerts[] = [
                'type'        => 'unsafe_electrical',
                'title'       => 'Équipement électrique non sûr',
                'description' => $equipment->reference_code . ' - ' . $equipment->playground->name,
                'date'        => now(),
                'action_url'  => route('equipment.show', $equipment),
            ];
        }
    }

    protected function calculateTrends()
    {
        // Tendance des risques sur les 12 derniers mois
        $this->riskTrendData = [];
        for ($i = 11; $i >= 0; $i--) {
            $startDate = now()->subMonths($i)->startOfMonth();
            $endDate   = now()->subMonths($i)->endOfMonth();

            $riskCount = RiskEvaluation::where('is_present', true)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->when($this->selectedPlayground, fn($q) => $q->where('playground_id', $this->selectedPlayground))
                ->count();

            $this->riskTrendData[] = [
                'month' => $startDate->format('M Y'),
                'risks' => $riskCount,
            ];
        }

        // Données de conformité par norme
        $this->complianceData = [
            ['norm' => 'EN 1176', 'compliance' => $this->normStats['EN 1176']['compliance_rate']],
            ['norm' => 'EN 1177', 'compliance' => $this->normStats['EN 1177']['compliance_rate']],
            ['norm' => 'EN 13814', 'compliance' => $this->normStats['EN 13814']['compliance_rate']],
            ['norm' => 'EN 60335', 'compliance' => $this->normStats['EN 60335']['compliance_rate']],
        ];
    }

    public function render()
    {
        $playgrounds = Playground::orderBy('name')->get();

        return view('livewire.multi-norm-dashboard', [
            'playgrounds' => $playgrounds,
        ]);
    }
}
