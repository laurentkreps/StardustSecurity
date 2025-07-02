<?php
// app/Console/Kernel.php - Mise à jour du scheduler
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\CheckOverdueMaintenanceCommand::class,
        Commands\CheckRiskEvaluationsCommand::class,
        Commands\CheckInspectionsDueCommand::class,
        Commands\CheckElectricalTestsCommand::class,
        Commands\GenerateAutomaticReportsCommand::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Vérifications de sécurité quotidiennes
        $schedule->command('maintenance:check-overdue')
            ->daily()
            ->at('08:00')
            ->emailOutputOnFailure('admin@example.com');

        $schedule->command('risks:check-evaluations')
            ->daily()
            ->at('08:30')
            ->emailOutputOnFailure('admin@example.com');

        $schedule->command('inspections:check-due')
            ->daily()
            ->at('09:00')
            ->emailOutputOnFailure('admin@example.com');

        $schedule->command('electrical:check-tests')
            ->daily()
            ->at('09:30')
            ->emailOutputOnFailure('admin@example.com');

        // Rapports automatiques
        $schedule->command('reports:generate-automatic --type=weekly')
            ->weekly()
            ->mondays()
            ->at('06:00');

        $schedule->command('reports:generate-automatic --type=monthly')
            ->monthly()
            ->at('05:00');

        $schedule->command('reports:generate-automatic --type=yearly')
            ->yearly()
            ->at('04:00');

        // Nettoyage des données
        $schedule->command('model:prune')
            ->daily()
            ->at('02:00');

        // Backup de la base de données
        $schedule->command('backup:run --only-db')
            ->daily()
            ->at('03:00');

        // Optimisation des performances
        $schedule->command('optimize:clear')
            ->weekly()
            ->sundays()
            ->at('01:00');
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
