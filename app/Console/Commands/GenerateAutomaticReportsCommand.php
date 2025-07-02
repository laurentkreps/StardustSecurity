<?php

// app/Console/Commands/GenerateAutomaticReportsCommand.php
namespace App\Console\Commands;

use App\Http\Controllers\ReportController;
use App\Models\Playground;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateAutomaticReportsCommand extends Command
{
    protected $signature   = 'reports:generate-automatic {--type=weekly}';
    protected $description = 'Générer automatiquement les rapports périodiques';

    public function handle()
    {
        $type = $this->option('type');
        $this->info("Génération des rapports automatiques: $type");

        $playgrounds = Playground::with(['equipment', 'riskEvaluations'])->get();

        foreach ($playgrounds as $playground) {
            try {
                $this->generatePlaygroundReports($playground, $type);
                $this->info("Rapports générés pour: {$playground->name}");
            } catch (\Exception $e) {
                $this->error("Erreur lors de la génération des rapports pour {$playground->name}: " . $e->getMessage());
            }
        }

        // Générer le rapport de synthèse global
        $this->generateGlobalSummary($type);

        return 0;
    }

    private function generatePlaygroundReports(Playground $playground, string $type)
    {
        $reportController = app(ReportController::class);

        switch ($type) {
            case 'weekly':
                // Rapport de conformité hebdomadaire
                $complianceReport = $reportController->complianceReport($playground);
                $this->saveReport($complianceReport, "weekly-compliance-{$playground->id}", $playground);
                break;

            case 'monthly':
                // Rapport d'analyse de risques mensuel
                $riskReport = $reportController->riskAnalysisReport($playground);
                $this->saveReport($riskReport, "monthly-risks-{$playground->id}", $playground);

                // Planning de maintenance
                $maintenanceReport = $reportController->maintenanceSchedule(request());
                $this->saveReport($maintenanceReport, "monthly-maintenance-{$playground->id}", $playground);
                break;

            case 'yearly':
                // Rapport complet annuel
                $fullReport = $reportController->multiNormSummary(request());
                $this->saveReport($fullReport, "yearly-summary-{$playground->id}", $playground);
                break;
        }
    }

    private function generateGlobalSummary(string $type)
    {
        $reportController = app(ReportController::class);
        $summaryReport    = $reportController->multiNormSummary(request());

        $filename = "global-summary-$type-" . now()->format('Y-m-d') . ".pdf";
        $path     = "reports/$type/$filename";

        Storage::put($path, $summaryReport->output());

        // Envoyer par email aux administrateurs
        $this->sendReportToAdministrators($path, "Rapport de synthèse $type");
    }

    private function saveReport($reportContent, string $basename, Playground $playground)
    {
        $filename = "$basename-" . now()->format('Y-m-d') . ".pdf";
        $path     = "reports/playground-{$playground->id}/$filename";

        Storage::put($path, $reportContent->output());

        // Envoyer par email au responsable de l'installation
        if ($playground->manager_contact) {
            $this->sendReportByEmail($path, $playground->manager_contact, $playground->name);
        }
    }

    private function sendReportToAdministrators(string $path, string $subject)
    {
        // Implementation simplified - in production, use proper mailing system
        $this->info("Rapport sauvegardé: $path (envoi email aux administrateurs)");
    }

    private function sendReportByEmail(string $path, string $email, string $playgroundName)
    {
        // Implementation simplified - in production, use proper mailing system
        $this->info("Rapport sauvegardé: $path (envoi email à $email pour $playgroundName)");
    }
}
