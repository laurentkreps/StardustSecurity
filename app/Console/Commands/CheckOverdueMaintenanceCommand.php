<?php

// app/Console/Commands/CheckOverdueMaintenanceCommand.php
namespace App\Console\Commands;

use App\Models\MaintenanceCheck;
use App\Models\User;
use App\Notifications\MaintenanceNotification;
use Illuminate\Console\Command;

class CheckOverdueMaintenanceCommand extends Command
{
    protected $signature   = 'maintenance:check-overdue';
    protected $description = 'Vérifier les maintenances en retard et envoyer des notifications';

    public function handle()
    {
        $this->info('Vérification des maintenances en retard...');

        // Marquer les maintenances en retard
        $overdueCount = MaintenanceCheck::where('scheduled_date', '<', now())
            ->whereNull('completed_date')
            ->where('status', '!=', 'overdue')
            ->update(['status' => 'overdue']);

        $this->info("$overdueCount maintenances marquées comme en retard.");

        // Trouver les maintenances critiques (plus de 7 jours de retard)
        $criticalOverdue = MaintenanceCheck::where('scheduled_date', '<', now()->subDays(7))
            ->whereNull('completed_date')
            ->where('status', 'overdue')
            ->with(['playground', 'equipment'])
            ->get();

        // Envoyer des notifications pour les maintenances critiques
        $notificationCount = 0;
        foreach ($criticalOverdue as $maintenance) {
            $users = User::where('role', 'manager')
                ->orWhere('playground_id', $maintenance->playground_id)
                ->get();

            foreach ($users as $user) {
                $user->notify(new MaintenanceNotification($maintenance, 'critical_overdue'));
                $notificationCount++;
            }
        }

        $this->info("$notificationCount notifications envoyées pour maintenances critiques.");

        return 0;
    }
}
