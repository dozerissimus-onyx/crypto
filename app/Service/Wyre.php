<?php


namespace App\Service;


use GuzzleHttp\Client;

class Wyre extends ApiWrapper
{
    const WALLET_DEBIT = 'DEBIT_CARD';
    const WALLET_APPLE = 'APPLE_PAY';

    protected static array $walletTypes = [
        self::WALLET_DEBIT,
        self::WALLET_APPLE
    ];

    const PAYMENT_DEBIT = 'debit-card';
    const PAYMENT_APPLE = 'apple-pay';

    protected static array $paymentMethods = [
        self::PAYMENT_DEBIT,
        self::PAYMENT_APPLE
    ];

    const CURRENCY_USD = 'USD';
    const CURRENCY_EUR = 'EUR';
    const CURRENCY_CAD = 'CAD';
    const CURRENCY_GBR = 'GBR';
    const CURRENCY_AUD = 'AUD';

    protected static array $currencies = [
        self::CURRENCY_USD,
        self::CURRENCY_EUR,
        self::CURRENCY_CAD,
        self::CURRENCY_GBR,
        self::CURRENCY_AUD
    ];

    const ACCOUNT_INDIVIDUAL = 'INDIVIDUAL';
    const ACCOUNT_BUSINESS = 'BUSINESS'; //Don't support yet

    protected static array $accountTypes = [
        self::ACCOUNT_INDIVIDUAL
    ];

    protected static array $accountCountries = ['US'];

    const ACCOUNT_FIELD_ID_INDIVIDUAL_LEGAL_NAME = 'individualLegalName';
    const ACCOUNT_FIELD_ID_INDIVIDUAL_CELLPHONE_NUMBER = 'individualCellphoneNumber';
    const ACCOUNT_FIELD_ID_INDIVIDUAL_EMAIL = 'individualEmail';
    const ACCOUNT_FIELD_ID_INDIVIDUAL_RESIDENCE_ADDRESS = 'individualResidenceAddress';
    const ACCOUNT_FIELD_ID_INDIVIDUAL_GOVERNMENT_ID = 'individualGovernmentId';
    const ACCOUNT_FIELD_ID_INDIVIDUAL_DATE_OF_BIRTH = 'individualDateOfBirth';
    const ACCOUNT_FIELD_ID_INDIVIDUAL_SSN = 'individualSsn';
    const ACCOUNT_FIELD_ID_INDIVIDUAL_SOURCE_OF_FUNDS = 'individualSourceOfFunds';
    const ACCOUNT_FIELD_ID_INDIVIDUAL_PROOF_OF_ADDRESS = 'individualProofOfAddress';
    const ACCOUNT_FIELD_STRING = 'STRING';
    const ACCOUNT_FIELD_CELLPHONE = 'CELLPHONE';
    const ACCOUNT_FIELD_EMAIL = 'EMAIL';
    const ACCOUNT_FIELD_ADDRESS = 'ADDRESS';
    const ACCOUNT_FIELD_DATE = 'DATE';
    const ACCOUNT_FIELD_DOCUMENT = 'DOCUMENT';
    const ACCOUNT_FIELD_PAYMENT_METHOD = 'PAYMENT_METHOD';

    protected static array $accountFieldTypes = [
        self::ACCOUNT_FIELD_ID_INDIVIDUAL_LEGAL_NAME => self::ACCOUNT_FIELD_STRING,
        self::ACCOUNT_FIELD_ID_INDIVIDUAL_CELLPHONE_NUMBER => self::ACCOUNT_FIELD_CELLPHONE,
        self::ACCOUNT_FIELD_ID_INDIVIDUAL_EMAIL => self::ACCOUNT_FIELD_EMAIL,
        self::ACCOUNT_FIELD_ID_INDIVIDUAL_RESIDENCE_ADDRESS => self::ACCOUNT_FIELD_ADDRESS,
        self::ACCOUNT_FIELD_ID_INDIVIDUAL_GOVERNMENT_ID => self::ACCOUNT_FIELD_DOCUMENT,
        self::ACCOUNT_FIELD_ID_INDIVIDUAL_DATE_OF_BIRTH => self::ACCOUNT_FIELD_DATE,
        self::ACCOUNT_FIELD_ID_INDIVIDUAL_SSN => self::ACCOUNT_FIELD_STRING,
        self::ACCOUNT_FIELD_ID_INDIVIDUAL_SOURCE_OF_FUNDS => self::ACCOUNT_FIELD_PAYMENT_METHOD,
        self::ACCOUNT_FIELD_ID_INDIVIDUAL_PROOF_OF_ADDRESS => self::ACCOUNT_FIELD_DOCUMENT
    ];

    const STATUS_OPEN = 'OPEN';
    const STATUS_PENDING = 'PENDING';
    const STATUS_APPROVED = 'APPROVED';

    protected static array $accountFieldStatuses = [
        self::STATUS_OPEN,
        self::STATUS_PENDING,
        self::STATUS_APPROVED
    ];

    public function __construct() {
        $this->client = new Client([
            'base_uri' => config('api.wyre.baseUri')
        ]);
    }

    protected function makeRequest($method, $uri)
    {
        $timestamp = floor(microtime(true)*1000);

        if(strpos($uri,"?"))
            $uri .= '&timestamp=' . sprintf("%d", $timestamp);
        else
            $uri .= '?timestamp=' . sprintf("%d", $timestamp);

        if(!empty($this->body))
            $this->body = json_encode($this->body, JSON_FORCE_OBJECT);
        else
            $this->body = '';

        $this->headers = [
            "Content-Type" => "application/json",
            "X-Api-Key" => config('api.wyre.key'),
            "X-Api-Signature" => $this->getSignature(config('api.wyre.secret'), config('api.wyre.baseUri') . $uri . $this->body)
        ];

        $response = $this->sendRequest($method, $uri);

        return json_decode($response->getBody(), true);
    }

    protected function getSignature($secret, $val) {
        $hash = hash_hmac('sha256', $val, $secret);
        return $hash;
    }

    /**
     * @param array $params
     * @param boolean $new
     * @return mixed
     */
    public function getWalletLimits($params = [], $new = false) {
        $this->body = [];
        $this->setParams($new ? $params : array_merge($this->params, $params));

        $this->body = [
            "accountId" => $this->params['accountId'] ?? '',
            "address" => [
                "street1" => $this->params['street1'] ?? "", // full street address
                "city" => $this->params['city'] ?? "",
                "state" => $this->params['state'] ?? "", // state code
                "postalCode" => $this->params['postalCode'] ?? "", // only numbers
                "country" => $this->params['country'] ?? "" // two digits country code
            ],
        ];

        if (isset($this->params['walletType']) && in_array($this->params['walletType'], self::$walletTypes)) {
            $this->body['walletType'] = $this->params['walletType'];
        }
        if (isset($this->params['sourceCurrency']) && in_array($this->params['sourceCurrency'], self::$currencies)) {
            $this->body['sourceCurrency'] = $this->params['sourceCurrency'];
        }
        if (isset($this->params['userId']) && $this->params['userId']) {
            $this->body['userId'] = $this->params['userId'];
        }

        return $this->makeRequest('POST', '/v3/widget/limits/calculate');
    }

    /**
     * @param array $params
     * @param boolean $new
     * @return mixed
     */
    public function walletOrderReservations(array $params = [], bool $new = false) {
        $this->body = [];
        $this->setParams($new ? $params : array_merge($this->params, $params));

        $lockFields = ["amount", "sourceCurrency", "destCurrency", "dest", "street1", "city", "state", "postalCode", "country", "firstName", "lastName", "phone", "email"];
        $possibleFields = [
            'amount', 'sourceCurrency', 'destCurrency', 'dest', 'firstName', 'lastName',
            'phone', 'email', 'country', 'postalCode', 'state', 'city', 'street1',
            'lockFields', 'redirectUrl', 'failureRedirectUrl', 'paymentMethod',
            'referrerAccountId', 'referenceId', 'hideTrackBtn', 'sourceAmount',
            'destAmount', 'amountIncludeFees'
        ];

        foreach ($possibleFields as $field) {
            switch ($field) {
                case 'sourceCurrency':
                    if (isset($this->params['sourceCurrency']) && in_array($this->params['sourceCurrency'], self::$currencies)) {
                        $this->body['sourceCurrency'] = $this->params['sourceCurrency'];
                    }
                    break;
                case 'paymentMethod':
                    if (isset($this->params['paymentMethod']) && in_array($this->params['paymentMethod'], self::$paymentMethods)) {
                        $this->body['paymentMethod'] = $this->params['paymentMethod'];
                    }
                    break;
                case 'lockFields':
                    if (isset($this->params['lockFields']) && !array_diff($this->params['lockFields'], $lockFields)) {
                        $this->body['lockFields'] = $this->params['lockFields'];
                    }
                    break;
                case 'hideTrackBtn' || 'amountIncludeFees' :
                    if (isset($this->params[$field])) {
                        $this->body[$field] = (boolean)$this->params[$field];
                    }
                    break;
                default:
                    if (isset($this->params[$field]) && $this->params[$field]) {
                        $this->body[$field] = $this->params[$field];
                    }
            }
        }

        return $this->makeRequest('POST', '/v3/orders/reserve');
    }

    /**
     * @param array $params
     * @param bool $new
     * @return mixed
     */
    public function walletOrderQuotation(array $params = [], bool $new = false) {
        $this->body = [];
        $this->setParams($new ? $params : array_merge($this->params, $params));

        $possibleFields = [
            'amount', 'sourceCurrency', 'destCurrency', 'dest', 'accountId',
            'walletType', 'amountIncludeFees', 'sourceAmount', 'destAmount'
        ];

        foreach ($possibleFields as $field) {
            switch ($field) {
                case 'sourceCurrency':
                    if (isset($this->params['sourceCurrency']) && in_array($this->params['sourceCurrency'], self::$currencies)) {
                        $this->body['sourceCurrency'] = $this->params['sourceCurrency'];
                    }
                    break;
                case 'walletType':
                    if (isset($this->params['walletType']) && in_array($this->params['walletType'], self::$walletTypes)) {
                        $this->body['walletType'] = $this->params['walletType'];
                    }
                    break;
                case 'amountIncludeFees' :
                    if (isset($this->params[$field])) {
                        $this->body[$field] = (boolean)$this->params[$field];
                    }
                    break;
                default:
                    if (isset($this->params[$field]) && $this->params[$field]) {
                        $this->body[$field] = $this->params[$field];
                    }
            }
        }

        return $this->makeRequest('POST', '/v3/orders/quote/partner');
    }

    /**
     * @param $orderId
     * @param bool $full
     * @return mixed
     */
    public function walletOrderDetails($orderId, bool $full = false) {
        return $this->makeRequest('GET', "/v3/orders/{$orderId}" . $full ? "/full" : "");
    }

    /**
     * @param $transferId
     * @return mixed
     */
    public function trackOrder($transferId) {
        return $this->makeRequest('GET', "/v2/transfer/{$transferId}/track");
    }

    /**
     * @return mixed
     */
    public function supportedCountries() {
        return $this->makeRequest('GET', "/v3/widget/supportedCountries");
    }

    /**
     * @param $reservationId
     * @return mixed
     */
    public function rateLockedReservation($reservationId) {
        return $this->makeRequest('GET', "/v3/orders/reservation/{$reservationId}");
    }

    /**
     * @param array $params
     * @param bool $new
     */
    public function createAccount(array $params = [], bool $new = false) {
        $this->setParams($new ? $params : array_merge($this->params, $params));

        $this->body = [
            'type' => isset($this->params['account']['type']) && in_array(strtoupper($this->params['account']['type']), self::$accountTypes) ?
                strtoupper($this->params['account']['type']) : self::ACCOUNT_INDIVIDUAL,
            'country' => isset($this->params['account']['country']) && in_array(strtoupper($this->params['account']['country']), self::$accountCountries) ?
                strtoupper($this->params['account']['type']) : 'US',
            'profileFields' => []
        ];

        foreach ($this->params['account']['profileFields'] ?? [] as $key => $field) {
            if (isset($field['fieldId']) && key_exists($field['fieldId'], self::$accountFieldTypes)) {
                $this->body['profileFields'][$key]['fieldId'] = $field['fieldId'];
                $this->body['profileFields'][$key]['fieldType'] = self::$accountFieldTypes[$field['fieldId']];
            }

            $this->body['profileFields'][$key]['value'] = $field['value'] ?? '';

            $this->body['profileFields'][$key]['note'] = $field['note'] ?? null;

            $this->body['profileFields'][$key]['status'] = isset($field['status']) && in_array($field['status'], self::$accountFieldStatuses) ?
                $field['status'] : self::STATUS_APPROVED;
        }

        if (isset($this->params['account']['referrerAccountId']) && $this->params['account']['referrerAccountId']) {
            $this->body['referrerAccountId'] = $this->params['account']['referrerAccountId'];
        }
        if (isset($this->params['account']['subaccount'])) {
            $this->body['subaccount'] = $this->params['account']['subaccount'];
        }
        if (isset($this->params['account']['disableEmail'])) {
            $this->body['disableEmail'] = $this->params['account']['disableEmail'];
        }

        return $this->makeRequest('POST', '/v3/accounts');
    }

    /**
     * @param $accountId
     * @return mixed
     */
    public function getAccount($accountId) {
        return $this->makeRequest('GET', "/v3/accounts/{$accountId}");
    }

    /**
     * @param string $accountId
     * @param array $profileFields
     * @return mixed
     */
    public function updateAccount(string $accountId, array $profileFields)
    {
        foreach ($profileFields as $key => $field) {
            if (isset($field['fieldId']) && key_exists($field['fieldId'], self::$accountFieldTypes)) {
                $this->body['profileFields'][$key]['fieldId'] = $field['fieldId'];
                $this->body['profileFields'][$key]['fieldType'] = self::$accountFieldTypes[$field['fieldId']];
            }

            $this->body['profileFields'][$key]['value'] = $field['value'] ?? '';

            $this->body['profileFields'][$key]['note'] = $field['note'] ?? null;

            $this->body['profileFields'][$key]['status'] = isset($field['status']) && in_array($field['status'], self::$accountFieldStatuses) ?
                $field['status'] : self::STATUS_APPROVED;
        }

        return $this->makeRequest('POST', "/v3/accounts/{$accountId}");
    }

    /**
     * @return mixed
     */
    public function limits() {
        return $this->makeRequest('GET', "/v3/limits");
    }

    /**
     * @param $accountId
     * @return mixed
     */
    public function getAccountStatusHistory($accountId) {
        return $this->makeRequest('GET', "/v3/accounts/{$accountId}/statusHistory");
    }

    /**
     * @param $accountId
     * @return mixed
     */
    public function getProfileFieldsStatus($accountId) {
        return $this->makeRequest('GET', "/v3/accounts/{$accountId}/profileFieldsStatuses");
    }

    /**
     * @param $accountId
     * @return mixed
     */
    public function getReferredAccounts($accountId) {
        return $this->makeRequest('GET', "/v3/accounts/{$accountId}/referredAccounts");
    }

    /**
     * @param $accountId
     * @param $fieldId
     * @return false|mixed
     */
    public function uploadDocument($accountId, $fieldId) {
        if (self::$accountFieldTypes[$fieldId] ?? null !== self::ACCOUNT_FIELD_DOCUMENT) {
            return false;
        }

        if (!in_array($fieldId, [self::ACCOUNT_FIELD_ID_INDIVIDUAL_GOVERNMENT_ID, self::ACCOUNT_FIELD_ID_INDIVIDUAL_PROOF_OF_ADDRESS])) {
            return false;
        }

        return $this->makeRequest('POST', "/v3/accounts/{$accountId}/{$fieldId}");
    }
}
