<?php

namespace App\Observers;

use App\Events\CompletedTaskCreated;
use App\Models\CompletedTask;

class CompletedTaskObserver
{
    public function created(CompletedTask $completedTask): void
    {
        $completedTaskByUser = $completedTask->user;

        if ($completedTaskByUser && ! $completedTask->isTypeReferralIncome() && ! $completedTask->isTypeAdmin() && ! $completedTask->isTypeChargeback()) {
            CompletedTaskCreated::dispatch($completedTask);
        }

        if (! $completedTaskByUser || ! $completedTaskByUser->referredBy || ! $completedTask->isAvailableForReferring()) {
            return;
        }

        $completedTaskByUser->referredBy->completedTasks()->create([
            'type' => CompletedTask::TYPE_REFERRAL_INCOME,
            'points' => $completedTask->points * CompletedTask::COMMISSION_PERCENT_REFERRAL,
            'data' => [
                'completed_task_id' => $completedTask->id,
            ],
        ]);
    }

    public function creating(CompletedTask $completedTask): void
    {
        $ip = isset($completedTask->data['user_ip']) ? $completedTask->data['user_ip'] : get_ip();
        $location = get_full_location($ip);

        $data = ['user_ip' => $ip];

        if (! is_null($location)) {
            $data['location'] = $location;
        }

        $completedTask->data = $completedTask->data ? array_merge($completedTask->data, $data) : $data;
    }
}
