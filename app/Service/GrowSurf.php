<?php


namespace App\Service;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;

class GrowSurf extends Facade
{
    /**
     * Make and send request
     *
     * @param string $method
     * @param string $uri
     * @param array $body
     * @return mixed
     */
    protected function makeRequest(string $method, string $uri, array $body = [])
    {
        $client = new Client([
            'base_uri' => config('api.growsurf.baseUri')
        ]);

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . config('api.growsurf.key')
        ];

        if(!empty($body))
            $body = json_encode($body, JSON_FORCE_OBJECT);
        else
            $body = '';

        try {
            $response = $client->request($method, $uri, [
                'headers' => $headers,
                'body' => $body,
            ]);
            if ($response->getStatusCode() !== 200 && $response->getStatusCode() !== 201) {
                // Some actions
            }
        } catch (RequestException $e) {
            $participantNotFound = strpos($e->getMessage(), 'PARTICIPANT_NOT_FOUND'); // GrowSurf return 400 instead 404 when participant not found

            if (! $participantNotFound && $e->getCode() !== 404) {
                Log::critical('GrowSurf Request Failed', ['message' => $e->getMessage()]);
            }

            $response = null;
        }

        return $response ? json_decode($response->getBody(), true) : [];
    }

    /**
     * Retrieves a campaign for the given ID
     *
     * @param string $campaignId
     * @return mixed
     */
    public function getCampaign(string $campaignId) {
        return $this->makeRequest('GET', "/v2/campaign/{$campaignId}");
    }

    /**
     * Retrieves a list of your campaigns. Campaigns that have been deleted will not be returned in this response
     *
     * @return mixed
     */
    public function getCampaigns() {
        return $this->makeRequest('GET', "/v2/campaigns");
    }

    /**
     * Retrieves a single participant from a campaign using the given participant ID or email
     *
     * @param string $campaignId
     * @param string $participant
     * @return array|mixed
     */
    public function getParticipant(string $campaignId, string $participant) {
        return $this->makeRequest('GET', "/v2/campaign/{$campaignId}/participant/{$participant}");
    }

    /**
     * Retrieves a list of participants in the campaign
     *
     * @param string $campaignId
     * @param array $args
     * @return array|mixed
     */
    public function getParticipants(string $campaignId, array $args = []) {
        $body = [];

        if (isset($args['nextId'])) {
            $body['nextId'] = $args['nextId'];
        }
        if (isset($args['limit'])) {
            $body['limit'] = $args['limit'];
        }

        return $this->makeRequest('GET', "/v2/campaign/{$campaignId}/participants", $body);
    }

    /**
     * Retrieves a list of participants in the campaign ordered by referral count in ascending order
     *
     * @param string $campaignId
     * @param array $args
     * @return array|mixed
     */
    public function getLeaderboard(string $campaignId, $args = []) {
        $body = [];

        if (isset($args['nextId'])) {
            $body['nextId'] = $args['nextId'];
        }
        if (isset($args['limit'])) {
            $body['limit'] = $args['limit'];
        }
        if (isset($args['isMonthly'])) {
            $body['isMonthly'] = $args['isMonthly'];
        }
        if (isset($args['leaderboardType'])) {
            $body['leaderboardType'] = $args['leaderboardType'];
        }

        return $this->makeRequest('GET', "/campaign/{$campaignId}/leaderboard", $body);
    }

    /**
     * Adds a participant to the campaign
     *
     * @param string $campaignId
     * @param string $participantEmail
     * @param array $args
     * @return array|mixed
     */
    public function addParticipant(string $campaignId, string $participantEmail, array $args = []) {
        $body = [];

        $body['email'] = $participantEmail;

        if (isset($args['referredBy'])) {
            $body['referredBy'] = $args['referredBy'];
        }
        if (isset($args['referralStatus'])) {
            $body['referralStatus'] = $args['referralStatus'];
        }
        if (isset($args['firstName'])) {
            $body['firstName'] = $args['firstName'];
        }
        if (isset($args['lastName'])) {
            $body['lastName'] = $args['lastName'];
        }
        if (isset($args['ipAddress'])) {
            $body['ipAddress'] = $args['ipAddress'];
        }
        if (isset($args['metadata'])) {
            $body['metadata'] = $args['metadata'];
        }

        return $this->makeRequest('POST', "/v2/campaign/{$campaignId}/participant", $body);
    }

    /**
     * Triggers a referral using an existing participant's ID or email, awarding referral credit to the referrer of the existing participant
     *
     * @param string $campaignId
     * @param string $participant
     * @return array|mixed
     */
    public function triggerReferralByParticipant(string $campaignId, string $participant) {
        return $this->makeRequest('POST', "/v2/campaign/{$campaignId}/participant/{$participant}/ref");
    }

    /**
     * Updates a participant within the campaign using ID or email of the participant
     *
     * @param string $campaignId
     * @param string $participant
     * @param array $args
     * @return array|mixed
     */
    public function updateParticipant(string $campaignId, string $participant, array $args = []) {
        $body = [];

        if (isset($args['referredBy'])) {
            $body['referredBy'] = $args['referredBy'];
        }
        if (isset($args['referralStatus'])) {
            $body['referralStatus'] = $args['referralStatus'];
        }
        if (isset($args['firstName'])) {
            $body['firstName'] = $args['firstName'];
        }
        if (isset($args['lastName'])) {
            $body['lastName'] = $args['lastName'];
        }
        if (isset($args['email'])) {
            $body['email'] = $args['email'];
        }
        if (isset($args['metadata'])) {
            $body['metadata'] = $args['metadata'];
        }

        return $this->makeRequest('POST', "/v2/campaign/{$campaignId}/participant/{$participant}", $body);
    }

    /**
     * Removes a participant within the campaign using ID or email of the participant
     *
     * @param string $campaignId
     * @param string $participant
     * @return array|mixed
     */
    public function removeParticiant(string $campaignId, string $participant) {
        return $this->makeRequest('DELETE', "/v2/campaign/{$campaignId}/participant/{$participant}");
    }

    /**
     * Retrieves a list of rewards earned by a participant ID or email
     *
     * @param string $campaignId
     * @param string $participant
     * @param array $args
     * @return array|mixed
     */
    public function getParticipantRewards(string $campaignId, string $participant, array $args = []) {
        $body = [];

        if (isset($args['nextId'])) {
            $body['nextId'] = $args['nextId'];
        }
        if (isset($args['limit'])) {
            $body['limit'] = $args['limit'];
        }

        return $this->makeRequest('GET', "/v2/campaign/{$campaignId}/participant/{$participant}/rewards", $body);
    }

    /**
     * Approve a reward that was earned by a participant
     *
     * @param string $campaignId
     * @param string $rewardId
     * @param array $args
     * @return array|mixed
     */
    public function approveParticipantReward(string $campaignId, string $rewardId, array $args = []) {
        $body = [];

        if (isset($args['fulfill'])) {
            $body['fulfill'] = (bool)$args['fulfill'];
        }
        return $this->makeRequest('POST', "/v2/campaign/{$campaignId}/reward/{$rewardId}/approve", $body);
    }

    /**
     * Fulfill a reward that was earned by a participant
     *
     * @param string $campaignId
     * @param string $rewardId
     * @return array|mixed
     */
    public function fulfillParticipantReward(string $campaignId, string $rewardId) {
        return $this->makeRequest('POST', "/v2/campaign/{$campaignId}/reward/{$rewardId}/fulfill");
    }

    /**
     * Remove a reward that was earned by a participant
     *
     * @param string $campaignId
     * @param string $rewardId
     * @return array|mixed
     */
    public function removeParticipantReward(string $campaignId, string $rewardId) {
        return $this->makeRequest('DELETE', "/v2/campaign/{$campaignId}/reward/{$rewardId}");
    }

    /**
     * Retrieves a list of all referrals and email invites made by participants in a campaign
     *
     * @param string $campaignId
     * @param array $args
     * @return array|mixed
     */
    public function getReferrals(string $campaignId, array $args = []) {
        $body = [];

        if (isset($args['sortBy'])) {
            $body['sortBy'] = $args['sortBy'];
        }
        if (isset($args['desc'])) {
            $body['desc'] = $args['desc'];
        }
        if (isset($args['limit'])) {
            $body['limit'] = $args['limit'];
        }
        if (isset($args['offset'])) {
            $body['offset'] = $args['offset'];
        }
        if (isset($args['email'])) {
            $body['email'] = $args['email'];
        }
        if (isset($args['firstName'])) {
            $body['firstName'] = $args['firstName'];
        }
        if (isset($args['lastName'])) {
            $body['lastName'] = $args['lastName'];
        }
        if (isset($args['referralStatus'])) {
            $body['referralStatus'] = $args['referralStatus'];
        }

        return $this->makeRequest('GET', "/v2/campaign/{$campaignId}/referrals", $body);
    }

    /**
     * Retrieves a list of all referrals and email invites made by a participant in a campaign using ID or email
     *
     * @param string $campaignId
     * @param string $participant
     * @param array $args
     * @return array|mixed
     */
    public function getParticipantReferrals(string $campaignId, string $participant, $args = []) {
        $body = [];

        if (isset($args['sortBy'])) {
            $body['sortBy'] = $args['sortBy'];
        }
        if (isset($args['desc'])) {
            $body['desc'] = $args['desc'];
        }
        if (isset($args['limit'])) {
            $body['limit'] = $args['limit'];
        }
        if (isset($args['offset'])) {
            $body['offset'] = $args['offset'];
        }
        if (isset($args['email'])) {
            $body['email'] = $args['email'];
        }
        if (isset($args['firstName'])) {
            $body['firstName'] = $args['firstName'];
        }
        if (isset($args['lastName'])) {
            $body['lastName'] = $args['lastName'];
        }
        if (isset($args['referralStatus'])) {
            $body['referralStatus'] = $args['referralStatus'];
        }

        return $this->makeRequest('GET', "/v2/campaign/{$campaignId}/participant/{$participant}/referrals", $body);
    }
}
