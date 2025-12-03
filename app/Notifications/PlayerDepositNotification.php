<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class PlayerDepositNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $deposit;

    public function __construct($deposit)
    {
        $this->deposit = $deposit;
        $this->deposit->loadMissing('user');
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        $payload = $this->payload();

        Log::info('Storing deposit notification in database:', $payload);

        return $payload;
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->payload());
    }

    private function payload(): array
    {
        $playerName = $this->deposit->user->user_name ?? $this->deposit->user->name ?? 'Player';

        return [
            'type' => 'deposit',
            'player_name' => $playerName,
            'amount' => $this->deposit->amount,
            'refrence_no' => $this->deposit->refrence_no,
            'message' => "Player {$playerName} has deposited {$this->deposit->amount}.",
            'deposit_request_id' => $this->deposit->id,
            'route' => route('admin.agent.deposit'),
        ];
    }
}
