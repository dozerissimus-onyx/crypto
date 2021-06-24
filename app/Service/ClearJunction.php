<?php


namespace App\Service;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

/**
 * Class EnigmaSecurities
 * @package App\Service
 */

class ClearJunction
{
    private $checkOnly = true;

    private function getSignature($body, $date) {
        return '';
    }

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
            'base_uri' => config('api.clearJunction.baseUri')
        ]);

        $date = date(DATE_ISO8601, time());

        if(!empty($body))
            $body = json_encode($body, JSON_FORCE_OBJECT);
        else
            $body = '';

        if ($this->checkOnly) {
            $uri .= '?checkOnly=true';
        }

        $headers = [
            'Content-Type' => 'application/json',
            'Date' => $date,
            'X-API-KEY' => config('api.clearJunction.key'),
            'Authorization' => 'Bearer ' . $this->getSignature($body, $date)
        ];

        try {
            $response = $client->request($method, $uri, [
                'headers' => $headers,
                'body' => $body,
//                'debug' => true
            ]);
            if ($response->getStatusCode() !== 200) {
                Log::critical('Clear Junction Request Failed', ['statusCode' => $response->getStatusCode(), 'message' => $response->getReasonPhrase()]);
                $response = null;
            }
        } catch (GuzzleException $e) {
            if ($e->getCode() !== 404) {
                Log::critical('Clear Junction Request Failed', ['statusCode' => $e->getCode(), 'message' => $e->getMessage()]);
            }

            $response = null;
        }

        return $response ? json_decode($response->getBody(), true) : [];
    }

    protected function setupPayoutBaseBody($args) {
        $body = [
            // required
            // string
            // Unique order ID assigned by client system
            'clientOrder' => $args['clientOrder'] ?? null,

            // required
            // string
            // The funds currency applied to the client account balance (account currency). Literal code according to ISO-4217
            'currency' => $args['currency'] ?? null,

            // required
            // number
            // The amount applied to the client account balance in the account currency.
            'amount' => $args['amount'] ?? null,

            // required
            // string
            // The description of the transaction (the attribute is required but can be an empty string)
            'description' => $args['description'] ?? '',
        ];

        if (isset($args['productName'])) {
            // string
            // The name of the product
            $body['productName'] = $args['productName'];
        }
        if (isset($args['siteAddress'])) {
            // string
            // The Url of the client's site
            $body['siteAddress'] = $args['siteAddress'];
        }
        if (isset($args['label'])) {
            // string
            // Any data that can be useful for identification order
            $body['label'] = $args['label'];
        }
        if (isset($args['postbackUrl'])) {
            // string
            // The URL that a notification message should be sent to
            $body['postbackUrl'] = $args['postbackUrl'];
        }
        if (isset($args['customInfo'])) {
            // customInfo
            $body['customInfo'] = $args['customInfo'];
        }
        if (isset($args['payer'])) {
            // array
            // The data of the payer. The structure is defined for each client personally.
            // Strictly one the section (individual or corporate) must be defined.
            $body['payer'] = [
                // string
                // The ID of the Customer in the Client's system.
                'clientCustomerId' => $args['payer']['clientCustomerId'] ?? null,

                // string
                // Client/Customer wallet UUID.
                // (It is used in payment scenarios. For details please consult with your integration manager.)
                'walletUuid' => $args['payer']['walletUuid'] ?? null,
            ];

            if (isset($args['payer']['individual'])) {
                // array
                // Personal data of individual customer. For more detail see IndividualUsEntity
                $body['payer']['individual'] = $args['payer']['individual'];
            } else {
                // array
                // Registration data of the company. For more detail see CorporateEntity
                $body['payer']['corporate'] = $args['payer']['corporate'];
            }
        }

        // required
        // array
        // The data of the payee. The structure is defined for each client personally.
        // Strictly one the section (individual or corporate) must be defined.
        $body['payee'] = [
            // string
            // The ID of the Customer in the Client's system.
            'clientCustomerId' => $args['payee']['clientCustomerId'] ?? null,

            // string
            // Client/Customer wallet UUID.
            // (It is used in payment scenarios. For details please consult with your integration manager.)
            'walletUuid' => $args['payee']['walletUuid'] ?? null,
        ];
        if (isset($args['payee']['individual'])) {
            // array
            // Personal data of individual customer. For more detail see IndividualUsEntity
            $body['payee']['individual'] = $args['payee']['individual'];
        } else {
            // array
            // Registration data of the company. For more detail see CorporateEntity
            $body['payee']['corporate'] = $args['payee']['corporate'];
        }

        return $body;
    }

    /**
     * Transfer of funds from the client balance to the specified account (bank or other financial institution)
     *
     * @param array $args
     * @return array|mixed
     */
    public function postPayoutToUSSwift($args = []) {
        $body = $this->setupPayoutBaseBody($args);

        // required
        // array
        // The payee banking details
        $body['payeeRequisite'] = [
            // required
            // string
            // Bank Swift Code according to ISO 9362
            'bankSwiftCode' => $args['payeeRequisite']['bankSwiftCode'] ?? null,

            // required
            // string
            // The number of the account that the funds should be transferred to
            'bankAccountNumber' => $args['payeeRequisite']['bankAccountNumber'] ?? null,
        ];

        if (isset($args['payeeRequisite']['bankName'])) {
            // string
            // Bank name
            $body['payeeRequisite']['bankName'] = $args['payeeRequisite']['bankName'];
        }

        if (isset($args['payeeRequisite']['intermediaryInstitution'])) {
            // array
            // Intermediary institution information
            $body['payeeRequisite']['intermediaryInstitution'] = [
                // required
                // string
                // Intermediary bank code
                'bankCode' => $args['payeeRequisite']['intermediaryInstitution']['bankCode'] ?? null,

                // required
                // string
                // Intermediary bank name
                'bankName' => $args['payeeRequisite']['intermediaryInstitution']['bankName'] ?? null,
            ];
        }

        if (isset($args['payerRequisite'])) {
            // array
            // The payer banking details
            $body['payerRequisite'] = [
                // required
                // string
                // Bank Swift Code according to ISO 9362
                'bankSwiftCode' => $args['payerRequisite']['bankSwiftCode'] ?? null,

                // required
                // string
                // The number of the account that the funds should be transferred to
                'bankAccountNumber' => $args['payerRequisite']['bankAccountNumber'] ?? null,
            ];

            if (isset($args['payerRequisite']['bankName'])) {
                // string
                // Bank name
                $body['payerRequisite']['bankName'] = $args['payerRequisite']['bankName'];
            }

            if (isset($args['payerRequisite']['intermediaryInstitution'])) {
                // array
                // Intermediary institution information
                $body['payerRequisite']['intermediaryInstitution'] = [
                    // required
                    // string
                    // Intermediary bank code
                    'bankCode' => $args['payerRequisite']['intermediaryInstitution']['bankCode'] ?? null,

                    // required
                    // string
                    // Intermediary bank name
                    'bankName' => $args['payerRequisite']['intermediaryInstitution']['bankName'] ?? null,
                ];
            }
        }

        return $this->makeRequest('POST', '/v7/gate/payout/bankTransfer/swift', $body);
    }

    /**
     * Transfer of funds from the client balance to the specified account (bank or other financial institution)
     *
     * @param array $args
     * @return array|mixed
     */
    public function postPayoutToUSFedwire($args = []) {
        $body = $this->setupPayoutBaseBody($args);

        // required
        // array
        // The payee banking details
        $body['payeeRequisite'] = [
            // required
            // string
            // The code of the bank in american banking system (ABA)
            'bankCode' => $args['payeeRequisite']['bankCode'] ?? null,

            // required
            // string
            // The number of the account that the funds should be transferred to
            'bankAccountNumber' => $args['payeeRequisite']['bankAccountNumber'] ?? null,
        ];

        if (isset($args['payerRequisite'])) {
            // array
            // The payer banking details
            $body['payerRequisite'] = [
                // required
                // string
                // The code of the bank in american banking system (ABA)
                'bankCode' => $args['payerRequisite']['bankCode'] ?? null,

                // required
                // string
                // The number of the account that the funds should be transferred to
                'bankAccountNumber' => $args['payerRequisite']['bankAccountNumber'] ?? null,
            ];
        }

        return $this->makeRequest('POST', '/v7/gate/payout/bankTransfer/fedwire', $body);
    }

    /**
     * Transfer of funds from the client balance to the specified account (bank or other financial institution)
     *
     * @param array $args
     * @return array|mixed
     */
    public function postPayoutInternalPayment($args = []) {
        $body = $this->setupPayoutBaseBody($args);

        // required
        // array
        // The payee banking details
        $body['payeeRequisite'] = [
            // required
            // string
            // The number of the account according to IBAN
            'iban' => $args['payeeRequisite']['iban'] ?? null,
        ];

        if (isset($args['payerRequisite'])) {
            // array
            // The payer banking details
            $body['payerRequisite'] = [
                // required
                // string
                // The number of the account according to IBAN
                'iban' => $args['payeeRequisite']['iban'] ?? null,
            ];
        }

        return $this->makeRequest('POST', '/v7/gate/payout/internalPayment', $body);
    }

    /**
     * Transfer of funds from the client balance to the specified account (bank or other financial institution)
     *
     * @param array $args
     * @return array|mixed
     */
    public function postPayoutToEUSCT($args = []) {
        $body = $this->setupPayoutBaseBody($args);

        // required
        // array
        // The payee banking details
        $body['payeeRequisite'] = [
            // required
            // string
            // The number of the account according to IBAN
            'iban' => $args['payeeRequisite']['iban'] ?? null,

            // required
            // string
            // Bank Swift Code according to ISO 9362
            'bankSwiftCode' => $args['payeeRequisite']['bankSwiftCode'] ?? null,
        ];

        if (isset($args['payerRequisite'])) {
            // array
            // The payer banking details
            $body['payerRequisite'] = [
                // required
                // string
                // The number of the account according to IBAN
                'iban' => $args['payerRequisite']['iban'] ?? null,

                // required
                // string
                // Bank Swift Code according to ISO 9362
                'bankSwiftCode' => $args['payerRequisite']['bankSwiftCode'] ?? null,
            ];
        }

        return $this->makeRequest('POST', '/v7/gate/payout/bankTransfer/eu', $body);
    }

    /**
     * Transfer of funds from the client balance to the specified account (bank or other financial institution)
     *
     * @param array $args
     * @return array|mixed
     */
    public function postPayoutToEUInstant($args = []) {
        $body = $this->setupPayoutBaseBody($args);

        // required
        // array
        // The payee banking details
        $body['payeeRequisite'] = [
            // required
            // string
            // The number of the account according to IBAN
            'iban' => $args['payeeRequisite']['iban'] ?? null,

            // required
            // string
            // Bank Swift Code according to ISO 9362
            'bankSwiftCode' => $args['payeeRequisite']['bankSwiftCode'] ?? null,
        ];

        if (isset($args['payerRequisite'])) {
            // array
            // The payer banking details
            $body['payerRequisite'] = [
                // required
                // string
                // The number of the account according to IBAN
                'iban' => $args['payerRequisite']['iban'] ?? null,

                // required
                // string
                // Bank Swift Code according to ISO 9362
                'bankSwiftCode' => $args['payerRequisite']['bankSwiftCode'] ?? null,
            ];
        }

        return $this->makeRequest('POST', '/gate/payout/bankTransfer/sepaInst', $body);
    }

    /**
     * Transfer of funds from the client balance to the specified account (bank or other financial institution)
     *
     * @param array $args
     * @return array|mixed
     */
    public function postPayoutToUKFasterPayments($args = []) {
        $body = $this->setupPayoutBaseBody($args);

        // required
        // array
        // The payee banking details
        $body['payeeRequisite'] = [
            // required
            // string
            // The name given by both the British and Irish banking industry to the bank codes which are used to route money transfers
            // between banks within their respective countries via their respective clearance organizations
            'sortCode' => $args['payeeRequisite']['sortCode'] ?? null,

            // required
            // string
            // The number of the bank account
            'accountNumber' => $args['payeeRequisite']['accountNumber'] ?? null,
        ];

        if (isset($args['payeeRequisite']['iban'])) {
            // string
            // The number of the account according to IBAN
            $body['payeeRequisite']['iban'] = $args['payeeRequisite']['iban'];
        }

        if (isset($args['payeeRequisite']['bankSwiftCode'])) {
            // string
            // Bank Swift Code according to ISO 9362
            $body['payeeRequisite']['bankSwiftCode'] = $args['payeeRequisite']['bankSwiftCode'];
        }

        if (isset($args['payerRequisite'])) {
            // array
            // The payee banking details
            $body['payerRequisite'] = [
                // required
                // string
                // The name given by both the British and Irish banking industry to the bank codes which are used to route money transfers
                // between banks within their respective countries via their respective clearance organizations
                'sortCode' => $args['payerRequisite']['sortCode'] ?? null,

                // required
                // string
                // The number of the bank account
                'accountNumber' => $args['payerRequisite']['accountNumber'] ?? null,
            ];

            if (isset($args['payerRequisite']['iban'])) {
                // string
                // The number of the account according to IBAN
                $body['payerRequisite']['iban'] = $args['payerRequisite']['iban'];
            }

            if (isset($args['payerRequisite']['bankSwiftCode'])) {
                // string
                // Bank Swift Code according to ISO 9362
                $body['payerRequisite']['bankSwiftCode'] = $args['payerRequisite']['bankSwiftCode'];
            }
        }

        return $this->makeRequest('POST', '/v7/gate/payout/bankTransfer/fps', $body);
    }

    /**
     * Transfer of funds from the client balance to the specified account (bank or other financial institution)
     *
     * @param array $args
     * @return array|mixed
     */
    public function postPayoutToUKCHAPS($args = []) {
        $body = $this->setupPayoutBaseBody($args);

        // required
        // array
        // The payee banking details
        $body['payeeRequisite'] = [
            // required
            // string
            // The name given by both the British and Irish banking industry to the bank codes which are used to route money transfers
            // between banks within their respective countries via their respective clearance organizations
            'sortCode' => $args['payeeRequisite']['sortCode'] ?? null,

            // required
            // string
            // The number of the bank account
            'accountNumber' => $args['payeeRequisite']['accountNumber'] ?? null,

            // required
            // string
            // Bank Swift Code according to ISO 9362
            'bankSwiftCode' => $args['payeeRequisite']['bankSwiftCode']
        ];

        if (isset($args['payeeRequisite']['iban'])) {
            // string
            // The number of the account according to IBAN
            $body['payeeRequisite']['iban'] = $args['payeeRequisite']['iban'];
        }

        if (isset($args['payerRequisite'])) {
            // array
            // The payee banking details
            $body['payerRequisite'] = [
                // required
                // string
                // The name given by both the British and Irish banking industry to the bank codes which are used to route money transfers
                // between banks within their respective countries via their respective clearance organizations
                'sortCode' => $args['payeeRequisite']['sortCode'] ?? null,

                // required
                // string
                // The number of the bank account
                'accountNumber' => $args['payeeRequisite']['accountNumber'] ?? null,

                // required
                // string
                // Bank Swift Code according to ISO 9362
                'bankSwiftCode' => $args['payeeRequisite']['bankSwiftCode']
            ];

            if (isset($args['payerRequisite']['iban'])) {
                // string
                // The number of the account according to IBAN
                $body['payerRequisite']['iban'] = $args['payerRequisite']['iban'];
            }
        }

        return $this->makeRequest('POST', '/v7/gate/payout/bankTransfer/chaps', $body);
    }

    /**
     * Transfer of funds from the client balance to the specified account (bank or other financial institution)
     *
     * @param array $args
     * @return array|mixed
     */
    public function postPayoutToRU($args = []) {
        $body = $this->setupPayoutBaseBody($args);

        // required
        // array
        // The payee banking details
        $body['payeeRequisite'] = [
            // required
            // string
            // The code of the bank in russian banking system
            'bic' => $args['payeeRequisite']['bic'] ?? null,

            // required
            // string
            // The number of the account that the funds should be transferred to
            'accountNumber' => $args['payeeRequisite']['accountNumber'] ?? null,
        ];

        if (isset($args['payeeRequisite']['bankCorrAccount'])) {
            // string
            // The bank corr account
            $body['payeeRequisite']['bankCorrAccount'] = $args['payeeRequisite']['bankCorrAccount'];
        }
        if (isset($args['payeeRequisite']['bankName'])) {
            // string
            // The name of the bank
            $body['payeeRequisite']['bankName'] = $args['payeeRequisite']['bankName'];
        }
        if (isset($args['payeeRequisite']['bankCity'])) {
            // string
            // City name of the bank
            $body['payeeRequisite']['bankCity'] = $args['payeeRequisite']['bankCity'];
        }

        return $this->makeRequest('POST', '/v7/gate/payout/bankTransfer/ru', $body);
    }

    /**
     * Transfer of funds from the client balance to the specified account (bank or other financial institution)
     *
     * @param array $args
     * @return array|mixed
     */
    public function postPayoutToUA($args = []) {
        $body = $this->setupPayoutBaseBody($args);

        // required
        // array
        // The payee banking details
        $body['payeeRequisite'] = [
            // required
            // string
            // The code of the bank in russian banking system
            'bic' => $args['payeeRequisite']['bic'] ?? null,

            // required
            // string
            // The number of the account that the funds should be transferred to
            'accountNumber' => $args['payeeRequisite']['accountNumber'] ?? null,

            // required
            // string
            // The name of the bank
            'bankName' => $args['payeeRequisite']['bankName'] ?? null
        ];

        return $this->makeRequest('POST', '/v7/gate/payout/bankTransfer/ua', $body);
    }

    /**
     * Transfer of funds from the client balance to the specified account (bank or other financial institution)
     *
     * @param array $args
     * @return array|mixed
     */
    public function postPayoutToMD($args = []) {
        $body = $this->setupPayoutBaseBody($args);

        // required
        // array
        // The payee banking details
        $body['payeeRequisite'] = [
            // required
            // string
            // The number of the account that the funds should be transferred to
            'accountNumber' => $args['payeeRequisite']['accountNumber'] ?? null,
        ];

        if (isset($args['payeeRequisite']['bic'])) {
            // string
            // The code of the bank in banking system
            $body['payeeRequisite']['bic'] = $args['payeeRequisite']['bic'];
        }
        if (isset($args['payeeRequisite']['bankName'])) {
            // string
            // The name of the bank
            $body['payeeRequisite']['bankName'] = $args['payeeRequisite']['bankName'];
        }
        if (isset($args['payeeRequisite']['bankCity'])) {
            // string
            // City name of the bank
            $body['payeeRequisite']['bankCity'] = $args['payeeRequisite']['bankCity'];
        }

        return $this->makeRequest('POST', '/v7/gate/payout/bankTransfer/ru', $body);
    }

    /**
     * @param $uuid
     * @return array|mixed
     */
    public function getPayoutStatusByOrderReference($uuid) {
        return $this->makeRequest('GET', "/v7/gate/status/payout/orderReference/{$uuid}");
    }

    /**
     * @param $orderId
     * @return array|mixed
     */
    public function getPayoutStatusByClientOrder($orderId) {
        return $this->makeRequest('GET', "/v7/gate/status/payout/clientOrder/{$orderId}");
    }
}
