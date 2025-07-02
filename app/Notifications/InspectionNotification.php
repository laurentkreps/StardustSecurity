<?php

// app/Notifications/InspectionNotification.php
namespace App\Notifications;

use App\Models\AmusementRideInspection;
use App\Models\Equipment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InspectionNotification extends Notification
{
    use Queueable;

    protected $equipment;
    protected $inspection;
    protected $type;

    public function __construct(Equipment $equipment, string $type, ?AmusementRideInspection $inspection = null)
    {
        $this->equipment  = $equipment;
        $this->type       = $type;
        $this->inspection = $inspection;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject($this->getSubject())
            ->greeting('Notification d\'inspection EN 13814')
            ->line($this->getMessage())
            ->line("Installation: {$this->equipment->playground->name}")
            ->line("Équipement: {$this->equipment->reference_code} - {$this->equipment->equipment_type}");

        if ($this->inspection) {
            $message->line("Date d'inspection: {$this->inspection->inspection_date->format('d/m/Y')}")
                ->line("Résultat: {$this->inspection->overall_result_label}");
        }

        return $message->action('Voir les détails', url("/equipment/{$this->equipment->id}"))
            ->line('Action requise selon la réglementation EN 13814.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'type'          => $this->type,
            'equipment_id'  => $this->equipment->id,
            'playground_id' => $this->equipment->playground_id,
            'inspection_id' => $this->inspection?->id,
            'message'       => $this->getMessage(),
            'action_url'    => url("/equipment/{$this->equipment->id}"),
        ];
    }

    private function getSubject()
    {
        return match ($this->type) {
            'inspection_due' => 'Inspection EN 13814 requise',
            'inspection_failed' => 'Inspection EN 13814 non conforme',
            'operation_suspended' => 'Exploitation suspendue suite à inspection',
            'daily_check_missing' => 'Contrôle quotidien manquant',
            default => 'Notification d\'inspection'
        };
    }

    private function getMessage()
    {
        return match ($this->type) {
            'inspection_due' => "L'équipement {$this->equipment->reference_code} nécessite une inspection EN 13814",
            'inspection_failed' => "L'inspection de l'équipement {$this->equipment->reference_code} révèle des non-conformités",
            'operation_suspended' => "L'exploitation de l'équipement {$this->equipment->reference_code} est suspendue",
            'daily_check_missing' => "Le contrôle quotidien de l'équipement {$this->equipment->reference_code} n'a pas été effectué",
            default => "Notification concernant l'équipement {$this->equipment->reference_code}"
        };
    }
}
