<?php
// app/Console/Commands/CheckRiskEvaluationsCommand.php
namespace App\Console\Commands;

use App\Models\RiskEvaluation;
use App\Models\User;
use App\Notifications\RiskAnalysisNotification;
use Illuminate\Console\Command;

class CheckRiskEvaluationsCommand extends Command
{
    protected $signature   = 'risks:check-evaluations';
    protected $description = 'Vérifier les évaluations de risques et envoyer des notifications';

    public function handle()
    {
        $this->info('Vérification des évaluations de risques...');

        // Trouver les nouveaux risques critiques (créés dans les dernières 24h)
        $newCriticalRisks = RiskEvaluation::where('risk_category', 5)
            ->where('is_present', true)
            ->where('created_at', '>=', now()->subDay())
            ->with(['playground', 'equipment', 'dangerCategory'])
            ->get();

        $this->info("Trouvé {$newCriticalRisks->count()} nouveaux risques critiques.");

        // Notifier les risques critiques
        foreach ($newCriticalRisks as $risk) {
            $users = User::where('role', 'safety_manager')
                ->orWhere('playground_id', $risk->playground_id)
                ->get();

            foreach ($users as $user) {
                $user->notify(new RiskAnalysisNotification($risk, 'critical_risk'));
            }
        }

        // Vérifier les mesures correctives en retard
        $overdueMeasures = RiskEvaluation::where('is_present', true)
            ->where('target_date', '<', now())
            ->where('measure_status', '!=', 'completed')
            ->with(['playground', 'equipment', 'dangerCategory'])
            ->get();

        $this->info("Trouvé {$overdueMeasures->count()} mesures correctives en retard.");

        // Notifier les mesures en retard
        foreach ($overdueMeasures as $risk) {
            $users = User::where('role', 'maintenance_manager')
                ->orWhere('playground_id', $risk->playground_id)
                ->get();

            foreach ($users as $user) {
                $user->notify(new RiskAnalysisNotification($risk, 'overdue_measures'));
            }
        }

        return 0;
    }
}
