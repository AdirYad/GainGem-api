<?php

namespace App\Notifications;

use App\Models\GiftCard;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GiftCardTransactionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Transaction $transaction;

    public function __construct(Transaction $transaction)
    {
        $transaction->load('giftCard');

        $this->transaction = $transaction;
    }

    public function via(User $user): array
    {
        return ['mail', 'database'];
    }

    public function toMail(User $user): MailMessage
    {
        /** @var GiftCard $giftCard */
        $giftCard = $this->transaction->giftCard;

        return (new MailMessage)
            ->subject("[EzRewards] Successful Reward Claim #{$this->transaction->id}")
            ->line('Thank you for using EzRewards!')
            ->line("You have successfully claimed \${$giftCard->value} {$this->transaction->formatted_provider} Gift Card for {$this->transaction->points} points. Please see your code below.")
            ->line($giftCard->code)
            ->line('Share the news with your friends!');
    }

    public function toArray(User $user): array
    {
        return [
            'transaction_id' => $this->transaction->id,
        ];
    }
}