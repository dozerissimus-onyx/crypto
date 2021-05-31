<?php

namespace App\Jobs;

use App\Service\GrowSurf;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddUserToGrowsurf implements ShouldQueue
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
    public function handle(Request $request)
    {
        //$request->campaignId or config('campaignId)
        $campaignId = $request->campaignId;
        $growSurf = new GrowSurf();

        if ($request->filled('invited_by')) {
            $response = $growSurf->getParticipant($campaignId, $request->invited_by);
            $riskLevel = $response['fraudRiskLevel'] ?? null;

            if (! empty($response) && (! $riskLevel || $riskLevel === 'LOW')) {
                //todo Update user's invited_by
                $referrer = $response['id'];
            }
        }

        $args = [
            'firstName' => $request->firstName ?? '',
            'LastName' => $request->lastName ?? '',
            'ipAddress' => $request->ip(),
            'metadata' => [
                //other form data
            ]
        ];

        if ($referrer ?? null) {
            $args['referredBy'] = $referrer;
        }

        $response = $growSurf->addParticipant($campaignId, $request->email, $args);

        if (! empty($response)) {
            $growsurfId = $response['id'];
            //todo update new user grow_surf_id
        }
    }
}
