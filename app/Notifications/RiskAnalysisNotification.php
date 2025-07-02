<?php
// app/Notifications/RiskAnalysisNotification.php
namespace App\Notifications;

use App\Models\RiskEvaluation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RiskAnalysisNotification extends Notification
{
    use Queueable;

    protected $riskEvaluation;
    protected $type;

    public function __construct(RiskEvaluation $riskEvaluation, string $type = 'critical_risk')
    {
        $this->riskEvaluation = $riskEvaluation;
        $this->type           = $type;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $risk = $this->riskEvaluation;

        return (new MailMessage)
            ->subject($this->getSubject())
            ->greeting('Alerte de sécurité')
            ->line($this->getMessage())
            ->line("Installation: {$risk->playground->name}")
            ->when($risk->equipment, function ($message) use ($risk) {
                return $message->line("Équipement: {$risk->equipment->reference_code}");
            })
            ->line("Catégorie de risque: {$risk->risk_category_label}")
            ->line("Valeur de risque: " . number_format($risk->risk_value, 1))
            ->line("Action requise: {$risk->action_required}")
            ->action('Voir les détails', url("/risk-evaluations/{$risk->id}"))
            ->line('Cette notification nécessite une action immédiate.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'type'               => $this->type,
            'risk_evaluation_id' => $this->riskEvaluation->id,
            'playground_id'      => $this->riskEvaluation->playground_id,
            'equipment_id'       => $this->riskEvaluation->equipment_id,
            'risk_category'      => $this->riskEvaluation->risk_category,
            'risk_value'         => $this->riskEvaluation->risk_value,
            'message'            => $this->getMessage(),
            'action_url'         => url("/risk-evaluations/{$this->riskEvaluation->id}"),
        ];
    }

    private function getSubject()
    {
        return match ($this->type) {
            'critical_risk' => 'Risque critique identifié',
            'high_risk' => 'Risque élevé identifié',
            'overdue_measures' => 'Mesures correctives en retard',
            default => 'Notification de risque'
        };
    }

    private function getMessage()
    {
        $risk = $this->riskEvaluation;

        return match ($this->type) {
            'critical_risk' => "Un risque critique a été identifié: {$risk->dangerCategory->title}",
            'high_risk' => "Un risque élevé nécessite votre attention: {$risk->dangerCategory->title}",
            'overdue_measures' => "Les mesures correctives pour le risque {$risk->dangerCategory->code} sont en retard",
            default => "Notification concernant le risque {$risk->dangerCategory->code}"
        };
    }
}
