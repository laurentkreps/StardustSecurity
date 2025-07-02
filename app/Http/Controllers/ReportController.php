<?php
// app/Http/Controllers/ReportController.php
namespace App\Http\Controllers;

use App\Models\AmusementRideInspection;
use App\Models\ElectricalSafetyTest;
use App\Models\Equipment;
use App\Models\IncidentReport;
use App\Models\MaintenanceCheck;
use App\Models\Playground;
use App\Models\RiskEvaluation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Rapport d'analyse de risques multi-normes
     */
    public function riskAnalysisReport(Playground $playground)
    {
        $playground->load([
            'equipment.riskEvaluations.dangerCategory',
            'riskEvaluations.dangerCategory',
            'equipment.certifications',
        ]);

        // Grouper les évaluations par norme
        $evaluationsByNorm = $playground->riskEvaluations()
            ->with('dangerCategory')
            ->where('is_present', true)
            ->get()
            ->groupBy(function ($evaluation) {
                $norm = $evaluation->dangerCategory->regulation_reference;
                if (str_contains($norm, 'EN 1176')) {
                    return 'EN 1176';
                }

                if (str_contains($norm, 'EN 1177')) {
                    return 'EN 1177';
                }

                if (str_contains($norm, 'EN 13814')) {
                    return 'EN 13814';
                }

                if (str_contains($norm, 'EN 60335')) {
                    return 'EN 60335';
                }

                return 'Autre';
            });

        // Statistiques par norme
        $normStats = [];
        foreach ($evaluationsByNorm as $norm => $evaluations) {
            $normStats[$norm] = [
                'total_risks'      => $evaluations->count(),
                'high_risks'       => $evaluations->where('risk_category', '>=', 4)->count(),
                'critical_risks'   => $evaluations->where('risk_category', 5)->count(),
                'avg_risk_value'   => $evaluations->avg('risk_value'),
                'overdue_measures' => $evaluations->where('target_date', '<', now())
                    ->where('measure_status', '!=', 'completed')->count(),
            ];
        }

        // Recommandations par norme
        $recommendations = $this->generateNormSpecificRecommendations($playground, $evaluationsByNorm);

        $data = [
            'playground'        => $playground,
            'evaluationsByNorm' => $evaluationsByNorm,
            'normStats'         => $normStats,
            'recommendations'   => $recommendations,
            'globalStats'       => [
                'total_equipment' => $playground->equipment->count(),
                'total_risks'     => $playground->riskEvaluations->where('is_present', true)->count(),
                'compliance_rate' => $this->calculateComplianceRate($playground),
            ],
            'generated_at'      => now(),
            'generated_by'      => auth()->user()->name ?? 'Système',
        ];

        $pdf = Pdf::loadView('reports.risk-analysis', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = "analyse-risques-{$playground->name}-" . now()->format('Y-m-d') . ".pdf";

        return $pdf->download($filename);
    }

    /**
     * Rapport de conformité multi-normes
     */
    public function complianceReport(Playground $playground)
    {
        $playground->load([
            'equipment.certifications',
            'equipment.amusementRideInspections' => function ($query) {
                $query->latest();
            },
            'equipment.electricalTests'          => function ($query) {
                $query->latest();
            },
        ]);

        // Analyse de conformité par norme
        $complianceByNorm = [
            'EN 1176'  => $this->analyzeEN1176Compliance($playground),
            'EN 1177'  => $this->analyzeEN1177Compliance($playground),
            'EN 13814' => $this->analyzeEN13814Compliance($playground),
            'EN 60335' => $this->analyzeEN60335Compliance($playground),
        ];

        // Score global de conformité
        $globalScore = $this->calculateGlobalComplianceScore($complianceByNorm);

        $data = [
            'playground'       => $playground,
            'complianceByNorm' => $complianceByNorm,
            'globalScore'      => $globalScore,
            'actionPlan'       => $this->generateComplianceActionPlan($complianceByNorm),
            'generated_at'     => now(),
            'generated_by'     => auth()->user()->name ?? 'Système',
        ];

        $pdf = Pdf::loadView('reports.compliance', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = "conformite-{$playground->name}-" . now()->format('Y-m-d') . ".pdf";

        return $pdf->download($filename);
    }

    /**
     * Rapport d'inspection EN 13814
     */
    public function inspectionReport(AmusementRideInspection $inspection)
    {
        $inspection->load(['equipment.playground', 'equipment.technicalData']);

        $data = [
            'inspection'         => $inspection,
            'equipment'          => $inspection->equipment,
            'playground'         => $inspection->equipment->playground,
            'technicalData'      => $inspection->equipment->technicalData,
            'complianceAnalysis' => $this->analyzeInspectionCompliance($inspection),
            'recommendations'    => $this->generateInspectionRecommendations($inspection),
            'generated_at'       => now(),
            'generated_by'       => auth()->user()->name ?? 'Système',
        ];

        $pdf = Pdf::loadView('reports.inspection-en13814', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = "inspection-EN13814-{$inspection->equipment->reference_code}-" .
        $inspection->inspection_date->format('Y-m-d') . ".pdf";

        return $pdf->download($filename);
    }

    /**
     * Rapport de test électrique EN 60335
     */
    public function electricalTestReport(ElectricalSafetyTest $test)
    {
        $test->load(['equipment.playground']);

        $data = [
            'test'               => $test,
            'equipment'          => $test->equipment,
            'playground'         => $test->equipment->playground,
            'standards'          => config('risk_analysis.electrical_standards', []),
            'complianceAnalysis' => $this->analyzeElectricalTestCompliance($test),
            'recommendations'    => $this->generateElectricalTestRecommendations($test),
            'generated_at'       => now(),
            'generated_by'       => auth()->user()->name ?? 'Système'
        ];

        $pdf = Pdf::loadView('reports.electrical-test-en60335', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = "test-electrique-EN60335-{$test->equipment->reference_code}-" .
        $test->test_date->format('Y-m-d') . ".pdf";

        return $pdf->download($filename);
    }

    /**
     * Planning de maintenance
     */
    public function maintenanceSchedule(Request $request)
    {
        $startDate    = $request->input('start_date', now()->format('Y-m-d'));
        $endDate      = $request->input('end_date', now()->addMonth()->format('Y-m-d'));
        $playgroundId = $request->input('playground_id');

        $query = MaintenanceCheck::with(['playground', 'equipment'])
            ->whereBetween('scheduled_date', [$startDate, $endDate]);

        if ($playgroundId) {
            $query->where('playground_id', $playgroundId);
        }

        $maintenanceSchedule = $query->orderBy('scheduled_date')->get();

        // Grouper par semaine
        $scheduleByWeek = $maintenanceSchedule->groupBy(function ($maintenance) {
            return $maintenance->scheduled_date->format('Y-W');
        });

        $data = [
            'scheduleByWeek'     => $scheduleByWeek,
            'period'             => [
                'start' => $startDate,
                'end'   => $endDate,
            ],
            'playgrounds'        => Playground::all(),
            'selectedPlayground' => $playgroundId ? Playground::find($playgroundId) : null,
            'summary'            => [
                'total_tasks'     => $maintenanceSchedule->count(),
                'overdue_tasks'   => $maintenanceSchedule->where('status', 'overdue')->count(),
                'completed_tasks' => $maintenanceSchedule->where('status', 'completed')->count(),
            ],
            'generated_at'       => now(),
            'generated_by'       => auth()->user()->name ?? 'Système',
        ];

        $pdf = Pdf::loadView('reports.maintenance-schedule', $data);
        $pdf->setPaper('A4', 'landscape');

        $filename = "planning-maintenance-" . now()->format('Y-m-d') . ".pdf";

        return $pdf->download($filename);
    }

    /**
     * Rapport de synthèse multi-normes
     */
    public function multiNormSummary(Request $request)
    {
        $playgrounds = Playground::with([
            'equipment.certifications',
            'equipment.amusementRideInspections' => function ($query) {
                $query->latest()->limit(1);
            },
            'equipment.electricalTests'          => function ($query) {
                $query->latest()->limit(1);
            },
            'riskEvaluations'                    => function ($query) {
                $query->where('is_present', true);
            },
        ])->get();

        // Statistiques globales par norme
        $normStatistics = [
            'EN 1176'  => $this->calculateNormStatistics('EN 1176', $playgrounds),
            'EN 1177'  => $this->calculateNormStatistics('EN 1177', $playgrounds),
            'EN 13814' => $this->calculateNormStatistics('EN 13814', $playgrounds),
            'EN 60335' => $this->calculateNormStatistics('EN 60335', $playgrounds),
        ];

        // Top 10 des risques critiques
        $criticalRisks = RiskEvaluation::with(['playground', 'equipment', 'dangerCategory'])
            ->where('is_present', true)
            ->where('risk_category', '>=', 4)
            ->orderBy('risk_value', 'desc')
            ->limit(10)
            ->get();

        // Incidents récents
        $recentIncidents = IncidentReport::with(['playground', 'equipment'])
            ->where('incident_date', '>=', now()->subMonth())
            ->orderBy('incident_date', 'desc')
            ->get();

        $data = [
            'playgrounds'      => $playgrounds,
            'normStatistics'   => $normStatistics,
            'criticalRisks'    => $criticalRisks,
            'recentIncidents'  => $recentIncidents,
            'globalIndicators' => [
                'total_playgrounds'       => $playgrounds->count(),
                'total_equipment'         => $playgrounds->sum(fn($p) => $p->equipment->count()),
                'average_compliance_rate' => $playgrounds->avg(fn($p) => $this->calculateComplianceRate($p)),
                'high_risk_facilities'    => $playgrounds->filter(fn($p) => $p->high_risk_count > 0)->count(),
            ],
            'generated_at'     => now(),
            'generated_by'     => auth()->user()->name ?? 'Système',
        ];

        $pdf = Pdf::loadView('reports.multi-norm-summary', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = "synthese-multi-normes-" . now()->format('Y-m-d') . ".pdf";

        return $pdf->download($filename);
    }

    /**
     * Méthodes privées d'analyse
     */
    private function generateNormSpecificRecommendations(Playground $playground, $evaluationsByNorm)
    {
        $recommendations = [];

        foreach ($evaluationsByNorm as $norm => $evaluations) {
            $recommendations[$norm] = match ($norm) {
                'EN 1176' => $this->generateEN1176Recommendations($evaluations),
                'EN 1177' => $this->generateEN1177Recommendations($evaluations),
                'EN 13814' => $this->generateEN13814Recommendations($evaluations),
                'EN 60335' => $this->generateEN60335Recommendations($evaluations),
                default => []
            };
        }

        return $recommendations;
    }

    private function analyzeEN1176Compliance(Playground $playground)
    {
        $playgroundEquipment = $playground->equipment()->where('equipment_category', 'playground_equipment')->get();

        $compliance = [
            'equipment_count'      => $playgroundEquipment->count(),
            'certified_equipment'  => $playgroundEquipment->filter(function ($equipment) {
                return $equipment->certifications()->valid()->where('norm_reference', 'like', '%EN 1176%')->exists();
            })->count(),
            'recent_inspections'   => $playgroundEquipment->filter(function ($equipment) {
                return $equipment->maintenanceChecks()->where('created_at', '>=', now()->subYear())->exists();
            })->count(),
            'surface_compliance'   => $this->checkSurfaceCompliance($playground),
            'spacing_compliance'   => $this->checkSpacingCompliance($playgroundEquipment),
            'age_group_compliance' => $this->checkAgeGroupCompliance($playgroundEquipment),
        ];

        $compliance['overall_score'] = $this->calculateComplianceScore($compliance);

        return $compliance;
    }

    private function analyzeEN13814Compliance(Playground $playground)
    {
        $rides = $playground->equipment()->where('equipment_category', 'amusement_ride')->get();

        $compliance = [
            'ride_count'           => $rides->count(),
            'recent_inspections'   => $rides->filter(function ($ride) {
                return $ride->amusementRideInspections()
                    ->where('inspection_date', '>=', now()->subMonth())
                    ->where('operation_authorized', true)
                    ->exists();
            })->count(),
            'daily_checks_current' => $rides->filter(function ($ride) {
                return $ride->amusementRideInspections()
                    ->where('inspection_type', 'daily_check')
                    ->where('inspection_date', '>=', now()->subDay())
                    ->exists();
            })->count(),
            'qualified_operators'  => $playground->qualifiedOperators()->qualified()->count(),
            'weather_monitoring'   => $playground->max_wind_speed ? true : false,
            'emergency_procedures' => true, // À implémenter selon les données disponibles
        ];

        $compliance['overall_score'] = $this->calculateComplianceScore($compliance);

        return $compliance;
    }

    private function analyzeEN60335Compliance(Playground $playground)
    {
        $electricalEquipment = $playground->equipment()->where('equipment_category', 'electrical_system')->get();

        $compliance = [
            'equipment_count'             => $electricalEquipment->count(),
            'recent_tests'                => $electricalEquipment->filter(function ($equipment) {
                return $equipment->electricalTests()
                    ->where('test_date', '>=', now()->subYear())
                    ->where('safe_to_use', true)
                    ->exists();
            })->count(),
            'protection_class_compliance' => $electricalEquipment->filter(function ($equipment) {
                return ! empty($equipment->protection_class);
            })->count(),
            'ip_rating_compliance'        => $electricalEquipment->filter(function ($equipment) {
                return ! empty($equipment->ip_rating);
            })->count(),
            'earthing_compliance'         => $electricalEquipment->filter(function ($equipment) {
                return $equipment->requires_earth_connection === true;
            })->count(),
        ];

        $compliance['overall_score'] = $this->calculateComplianceScore($compliance);

        return $compliance;
    }

    private function calculateComplianceRate(Playground $playground)
    {
        // Implémentation simplifiée - à ajuster selon les critères métier
        $totalCriteria = 0;
        $metCriteria   = 0;

        // Critères généraux
        $totalCriteria += 5;
        $metCriteria += $playground->last_analysis_date && $playground->last_analysis_date->addYear()->isFuture() ? 1 : 0;
        $metCriteria += $playground->license_expiry && $playground->license_expiry->isFuture() ? 1 : 0;
        $metCriteria += $playground->equipment->where('status', 'active')->count() > 0 ? 1 : 0;
        $metCriteria += $playground->riskEvaluations->where('risk_category', '>=', 4)->count() === 0 ? 1 : 0;
        $metCriteria += $playground->maintenanceChecks->where('status', 'overdue')->count() === 0 ? 1 : 0;

        return $totalCriteria > 0 ? round(($metCriteria / $totalCriteria) * 100, 1) : 0;
    }

    private function calculateComplianceScore($compliance)
    {
        // Calcul générique d'un score de conformité
        $totalItems     = 0;
        $compliantItems = 0;

        foreach ($compliance as $key => $value) {
            if ($key === 'overall_score') {
                continue;
            }

            if (is_numeric($value) && str_contains($key, '_count')) {
                $totalItems += $value;
            } elseif (is_numeric($value)) {
                $compliantItems += $value;
            } elseif (is_bool($value)) {
                $totalItems += 1;
                $compliantItems += $value ? 1 : 0;
            }
        }

        return $totalItems > 0 ? round(($compliantItems / $totalItems) * 100, 1) : 0;
    }

    private function checkSurfaceCompliance(Playground $playground)
    {
        // Vérification basique - à étendre selon EN 1177
        return $playground->equipment->filter(function ($equipment) {
            return $equipment->requires_fall_protection && $equipment->fall_height > 0;
        })->count() > 0;
    }

    private function checkSpacingCompliance($equipment)
    {
                     // Vérification simplifiée des distances de sécurité
        return true; // À implémenter selon les données géographiques
    }

    private function checkAgeGroupCompliance($equipment)
    {
        // Vérification des groupes d'âge selon EN 1176
        return $equipment->filter(function ($eq) {
            return ! empty($eq->age_group);
        })->count();
    }

    private function calculateNormStatistics($norm, $playgrounds)
    {
        $stats = [
            'applicable_facilities' => 0,
            'compliant_facilities'  => 0,
            'total_equipment'       => 0,
            'high_risk_count'       => 0,
            'recent_incidents'      => 0,
        ];

        foreach ($playgrounds as $playground) {
            if (in_array($norm, $playground->applicable_norms)) {
                $stats['applicable_facilities']++;

                // Compter les équipements concernés par cette norme
                $relevantEquipment = $playground->equipment->filter(function ($equipment) use ($norm) {
                    return $this->isEquipmentSubjectToNorm($equipment, $norm);
                });

                $stats['total_equipment'] += $relevantEquipment->count();

                // Compter les risques élevés pour cette norme
                $highRisks = $playground->riskEvaluations
                    ->where('is_present', true)
                    ->where('risk_category', '>=', 4)
                    ->filter(function ($risk) use ($norm) {
                        return str_contains($risk->dangerCategory->regulation_reference, $norm);
                    });

                $stats['high_risk_count'] += $highRisks->count();

                // Incidents récents liés à cette norme
                $recentIncidents = $playground->incidentReports
                    ->where('incident_date', '>=', now()->subMonth())
                    ->filter(function ($incident) use ($norm, $relevantEquipment) {
                        return $incident->equipment && $relevantEquipment->contains($incident->equipment);
                    });

                $stats['recent_incidents'] += $recentIncidents->count();

                // Déterminer si l'installation est conforme pour cette norme
                if ($this->isFacilityCompliantForNorm($playground, $norm)) {
                    $stats['compliant_facilities']++;
                }
            }
        }

        return $stats;
    }

    private function isEquipmentSubjectToNorm($equipment, $norm)
    {
        return match ($norm) {
            'EN 1176', 'EN 1177' => $equipment->equipment_category === 'playground_equipment',
            'EN 13814'           => $equipment->equipment_category === 'amusement_ride',
            'EN 60335'           => $equipment->equipment_category === 'electrical_system',
            default              => false
        };
    }

    private function isFacilityCompliantForNorm($playground, $norm)
    {
        // Implémentation simplifiée - à ajuster selon les critères de chaque norme
        switch ($norm) {
            case 'EN 1176':
                return $this->analyzeEN1176Compliance($playground)['overall_score'] >= 80;
            case 'EN 13814':
                return $this->analyzeEN13814Compliance($playground)['overall_score'] >= 80;
            case 'EN 60335':
                return $this->analyzeEN60335Compliance($playground)['overall_score'] >= 80;
            default:
                return true;
        }
    }

    // Méthodes de génération de recommandations spécifiques
    private function generateEN1176Recommendations($evaluations)
    {
        $recommendations = [];

        $criticalRisks = $evaluations->where('risk_category', '>=', 4);
        if ($criticalRisks->count() > 0) {
            $recommendations[] = "Traiter en priorité les {$criticalRisks->count()} risques critiques identifiés selon EN 1176.";
        }

        $fallRisks = $evaluations->filter(function ($eval) {
            return str_contains(strtolower($eval->dangerCategory->title), 'chute');
        });

        if ($fallRisks->count() > 0) {
            $recommendations[] = "Vérifier les sols amortissants selon EN 1177 pour les {$fallRisks->count()} risques de chute identifiés.";
        }

        return $recommendations;
    }

    private function generateEN13814Recommendations($evaluations)
    {
        $recommendations = [];

        $structuralRisks = $evaluations->filter(function ($eval) {
            return str_contains(strtolower($eval->dangerCategory->title), 'structur');
        });

        if ($structuralRisks->count() > 0) {
            $recommendations[] = "Effectuer des contrôles structurels renforcés selon EN 13814.";
        }

        return $recommendations;
    }

    private function generateEN60335Recommendations($evaluations)
    {
        $recommendations = [];

        $electricalRisks = $evaluations->where('risk_category', '>=', 3);
        if ($electricalRisks->count() > 0) {
            $recommendations[] = "Programmer des tests électriques selon EN 60335 pour réduire les risques identifiés.";
        }

        return $recommendations;
    }

    private function generateEN1177Recommendations($evaluations)
    {
        return ["Vérifier régulièrement l'épaisseur et la qualité des sols amortissants selon EN 1177."];
    }
}

// app/Services/ReportGenerationService.php
namespace App\Services;

use App\Models\Playground;

class ReportGenerationService
{
    /**
     * Générer un rapport personnalisé
     */
    public function generateCustomReport($template, $data, $format = 'pdf')
    {
        switch ($format) {
            case 'pdf':
                return $this->generatePdfReport($template, $data);
            case 'excel':
                return $this->generateExcelReport($template, $data);
            case 'word':
                return $this->generateWordReport($template, $data);
            default:
                throw new \InvalidArgumentException("Format non supporté: {$format}");
        }
    }

    /**
     * Générer automatiquement tous les rapports pour une installation
     */
    public function generateAllReports(Playground $playground)
    {
        $reports = [];

        // Rapport d'analyse de risques
        $reports['risk_analysis'] = app(ReportController::class)->riskAnalysisReport($playground);

        // Rapport de conformité
        $reports['compliance'] = app(ReportController::class)->complianceReport($playground);

        // Planning de maintenance
        $reports['maintenance'] = app(ReportController::class)->maintenanceSchedule(request());

        return $reports;
    }

    /**
     * Planifier la génération automatique de rapports
     */
    public function scheduleAutomaticReports()
    {
        // À implémenter avec Laravel Scheduler
        // Génération mensuelle des rapports de conformité
        // Génération hebdomadaire des plannings de maintenance
        // Génération annuelle des synthèses multi-normes
    }

    private function generatePdfReport($template, $data)
    {
        return \Barryvdh\DomPDF\Facade\Pdf::loadView($template, $data);
    }

    private function generateExcelReport($template, $data)
    {
        // À implémenter avec Laravel Excel
        throw new \Exception('Export Excel non encore implémenté');
    }

    private function generateWordReport($template, $data)
    {
        // À implémenter avec PHPWord
        throw new \Exception('Export Word non encore implémenté');
    }
}
