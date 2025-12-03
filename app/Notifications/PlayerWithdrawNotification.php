<?php

namespace App\Notifications;

use App\Models\WithDrawRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class PlayerWithdrawNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private WithDrawRequest $withdraw)
    {
        $this->withdraw->loadMissing('user');
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return $this->payload();
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->payload());
    }

    private function payload(): array
    {
        $playerName = $this->withdraw->user->user_name ?? $this->withdraw->user->name ?? 'Player';
        $amount = $this->withdraw->amount;

        return [
            'type' => 'withdraw',
            'player_name' => $playerName,
            'amount' => $amount,
            'message' => "Player {$playerName} requested a withdraw of {$amount} Ks.",
            'withdraw_request_id' => $this->withdraw->id,
            'route' => route('admin.agent.withdraw'),
        ];
    }
}

