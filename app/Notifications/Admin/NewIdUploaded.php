<?php

namespace App\Notifications\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class NewIdUploaded extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function via(object $notifiable): array
    {
        // Added 'mail' channel
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $userName = "{$this->user->first_name} {$this->user->last_name}";

        return (new MailMessage)
                    ->subject('New ID Verification Required - ClearOptics Admin')
                    ->greeting('Hello Admin,')
                    ->line("**{$userName}** has uploaded a new ID for verification.")
                    ->line('Please review the uploaded document to approve or reject their booking privileges.')
                    ->action('Review Pending IDs', route('admin.users', ['filter_status' => 'pending_id']));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'id_verification',
            'message' => "{$this->user->first_name} {$this->user->last_name} has uploaded an ID for verification.",
            'url' => route('admin.users', ['filter_status' => 'pending_id'])
        ];
    }
}