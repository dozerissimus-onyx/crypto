<?php


namespace App\Service;


use GuzzleHttp\Client;

class GrowSurf extends ApiWrapper
{
    public function __construct() {
        $this->client = new Client([
            'base_uri' => config('api.growsurf.baseUri')
        ]);
    }

    /**
     * @param string $method
     * @param string $uri
     * @return mixed
     */
    protected function makeRequest(string $method, string $uri)
    {
        $this->headers = [
            'Authorization' => 'Bearer ' . config('api.growsurf.key')
        ];

        if(!empty($this->body))
            $this->body = json_encode($this->body, JSON_HEX_APOS);
        else
            $this->body = '';

//        $this->body = '{email: "sergey.o@oobit.com"}';
        $response = $this->sendRequest($method, $uri);

        return $response ? json_decode($response->getBody(), true) : [];
    }

    /**
     * Retrieves a campaign for the given ID
     *
     * @param string $campaignId
     * @return mixed
     */
    public function getCampaign(string $campaignId) {
        $this->body = [];
        return $this->makeRequest('GET', "/v2/campaign/{$campaignId}");
    }

    /**
     * Retrieves a list of your campaigns. Campaigns that have been deleted will not be returned in this response
     *
     * @return mixed
     */
    public function getCampaigns() {
        $this->body = [];
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
        $this->body = [];
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
        $this->body = [];

        if (isset($args['nextId'])) {
            $this->body['nextId'] = $args['nextId'];
        }
        if (isset($args['limit'])) {
            $this->body['limit'] = $args['limit'];
        }

        return $this->makeRequest('GET', "/v2/campaign/{$campaignId}/participants");
    }

    /**
     * Retrieves a list of participants in the campaign ordered by referral count in ascending order
     *
     * @param string $campaignId
     * @param array $args
     * @return array|mixed
     */
    public function getLeaderboard(string $campaignId, $args = []) {
        $this->body = [];

        if (isset($args['nextId'])) {
            $this->body['nextId'] = $args['nextId'];
        }
        if (isset($args['limit'])) {
            $this->body['limit'] = $args['limit'];
        }
        if (isset($args['isMonthly'])) {
            $this->body['isMonthly'] = $args['isMonthly'];
        }
        if (isset($args['leaderboardType'])) {
            $this->body['leaderboardType'] = $args['leaderboardType'];
        }

        return $this->makeRequest('GET', "/campaign/{$campaignId}/leaderboard");
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
        $this->body = [];

        $this->body['email'] = $participantEmail;

        if (isset($args['referredBy'])) {
            $this->body['referredBy'] = $args['referredBy'];
        }
        if (isset($args['referralStatus'])) {
            $this->body['referralStatus'] = $args['referralStatus'];
        }
        if (isset($args['firstName'])) {
            $this->body['firstName'] = $args['firstName'];
        }
        if (isset($args['lastName'])) {
            $this->body['lastName'] = $args['lastName'];
        }
        if (isset($args['ipAddress'])) {
            $this->body['ipAddress'] = $args['ipAddress'];
        }
        if (isset($args['metadata'])) {
            $this->body['metadata'] = $args['metadata'];
        }

        return $this->makeRequest('POST', "/v2/campaign/{$campaignId}/participant");
    }

    /**
     * Triggers a referral using an existing participant's ID or email, awarding referral credit to the referrer of the existing participant
     *
     * @param string $campaignId
     * @param string $participant
     * @return array|mixed
     */
    public function triggerReferralByParticipant(string $campaignId, string $participant) {
        $this->body = [];
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
        $this->body = [];

        if (isset($args['referredBy'])) {
            $this->body['referredBy'] = $args['referredBy'];
        }
        if (isset($args['referralStatus'])) {
            $this->body['referralStatus'] = $args['referralStatus'];
        }
        if (isset($args['firstName'])) {
            $this->body['firstName'] = $args['firstName'];
        }
        if (isset($args['lastName'])) {
            $this->body['lastName'] = $args['lastName'];
        }
        if (isset($args['email'])) {
            $this->body['email'] = $args['email'];
        }
        if (isset($args['metadata'])) {
            $this->body['metadata'] = $args['metadata'];
        }

        return $this->makeRequest('POST', "/v2/campaign/{$campaignId}/participant/{$participant}");
    }

    /**
     * Removes a participant within the campaign using ID or email of the participant
     *
     * @param string $campaignId
     * @param string $participant
     * @return array|mixed
     */
    public function removeParticiant(string $campaignId, string $participant) {
        $this->body = [];
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
        $this->body = [];

        if (isset($args['nextId'])) {
            $this->body['nextId'] = $args['nextId'];
        }
        if (isset($args['limit'])) {
            $this->body['limit'] = $args['limit'];
        }

        return $this->makeRequest('GET', "/v2/campaign/{$campaignId}/participant/{$participant}/rewards");
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
        $this->body = [];

        if (isset($args['fulfill'])) {
            $this->body['fulfill'] = (bool)$args['fulfill'];
        }
        return $this->makeRequest('POST', "/v2/campaign/{$campaignId}/reward/{$rewardId}/approve");
    }

    /**
     * Fulfill a reward that was earned by a participant
     *
     * @param string $campaignId
     * @param string $rewardId
     * @return array|mixed
     */
    public function fulfillParticipantReward(string $campaignId, string $rewardId) {
        $this->body = [];
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
        $this->body = [];
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
        $this->body = [];

        if (isset($args['sortBy'])) {
            $this->body['sortBy'] = $args['sortBy'];
        }
        if (isset($args['desc'])) {
            $this->body['desc'] = $args['desc'];
        }
        if (isset($args['limit'])) {
            $this->body['limit'] = $args['limit'];
        }
        if (isset($args['offset'])) {
            $this->body['offset'] = $args['offset'];
        }
        if (isset($args['email'])) {
            $this->body['email'] = $args['email'];
        }
        if (isset($args['firstName'])) {
            $this->body['firstName'] = $args['firstName'];
        }
        if (isset($args['lastName'])) {
            $this->body['lastName'] = $args['lastName'];
        }
        if (isset($args['referralStatus'])) {
            $this->body['referralStatus'] = $args['referralStatus'];
        }

        return $this->makeRequest('GET', "/v2/campaign/{$campaignId}/referrals");
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
        $this->body = [];

        if (isset($args['sortBy'])) {
            $this->body['sortBy'] = $args['sortBy'];
        }
        if (isset($args['desc'])) {
            $this->body['desc'] = $args['desc'];
        }
        if (isset($args['limit'])) {
            $this->body['limit'] = $args['limit'];
        }
        if (isset($args['offset'])) {
            $this->body['offset'] = $args['offset'];
        }
        if (isset($args['email'])) {
            $this->body['email'] = $args['email'];
        }
        if (isset($args['firstName'])) {
            $this->body['firstName'] = $args['firstName'];
        }
        if (isset($args['lastName'])) {
            $this->body['lastName'] = $args['lastName'];
        }
        if (isset($args['referralStatus'])) {
            $this->body['referralStatus'] = $args['referralStatus'];
        }

        return $this->makeRequest('GET', "/v2/campaign/{$campaignId}/participant/{$participant}/referrals");
    }
}
