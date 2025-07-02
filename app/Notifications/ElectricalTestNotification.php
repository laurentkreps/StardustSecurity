<?php

// app/Notifications/ElectricalTestNotification.php
namespace App\Notifications;

use App\Models\ElectricalSafetyTest;
use App\Models\Equipment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ElectricalTestNotification extends Notification
{
    use Queueable;

    protected $equipment;
    protected $test;
    protected $type;

    public function __construct(Equipment $equipment, string $type, ?ElectricalSafetyTest $test = null)
    {
        $this->equipment = $equipment;
        $this->type      = $type;
        $this->test      = $test;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject($this->getSubject())
            ->greeting('Notification de sécurité électrique EN 60335')
            ->line($this->getMessage())
            ->line("Installation: {$this->equipment->playground->name}")
            ->line("Équipement: {$this->equipment->reference_code} - {$this->equipment->equipment_type}");

        if ($this->test) {
            $message->line("Date de test: {$this->test->test_date->format('d/m/Y')}")
                ->line("Résultat: {$this->test->test_result_label}")
                ->line("Sûr à utiliser: " . ($this->test->safe_to_use ? 'Oui' : 'Non'));
        }

        return $message->action('Voir les détails', url("/equipment/{$this->equipment->id}"))
            ->line('Conformité EN 60335 requise pour la sécurité électrique.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'type'          => $this->type,
            'equipment_id'  => $this->equipment->id,
            'playground_id' => $this->equipment->playground_id,
            'test_id'       => $this->test?->id,
            'message'       => $this->getMessage(),
            'action_url'    => url("/equipment/{$this->equipment->id}"),
        ];
    }

    private function getSubject()
    {
        return match ($this->type) {
            'test_due' => 'Test électrique EN 60335 requis',
            'test_failed' => 'Test électrique EN 60335 non conforme',
            'unsafe_equipment' => 'Équipement électrique non sûr',
            'certificate_expiring' => 'Certificat électrique expirant',
            default => 'Notification de test électrique'
        };
    }

    private function getMessage()
    {
        return match ($this->type) {
            'test_due' => "L'équipement {$this->equipment->reference_code} nécessite un test électrique EN 60335",
            'test_failed' => "Le test électrique de l'équipement {$this->equipment->reference_code} révèle des défaillances",
            'unsafe_equipment' => "L'équipement {$this->equipment->reference_code} n'est pas sûr électriquement",
            'certificate_expiring' => "Le certificat électrique de l'équipement {$this->equipment->reference_code} expire bientôt",
            default => "Notification concernant l'équipement {$this->equipment->reference_code}"
        ];
    };
}
