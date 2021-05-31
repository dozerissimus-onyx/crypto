<?php

namespace App\Jobs;

use App\Service\GrowSurf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ApproveReward implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $growSurf = new GrowSurf();

        //todo get grow_surf_id from user (referral)

        $participantId = '';
        $campaignId = config('api.growsurf.campaignId'); //maybe from DB
        $rewardId = config('api.growsurf.rewardId'); //maybe from DB

        $rewards = $growSurf->getParticipantRewards($campaignId, $participantId);

        foreach ($rewards as $reward) {
            if ($reward['rewardId'] === $rewardId && $reward['status'] === 'PENDING') {
                $growSurf->approveParticipantReward($campaignId, $reward['id'], ['fulfill' => true]);
                break;
            }
        }
    }
}
