<?php


namespace App\Service;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class EnigmaSecurities
{
    protected $authKey;

    /**
     * EnigmaSecurities constructor.
     * Make object and auth
     */
    public function __construct() {
        $this->authKey = $this->login();
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
            'base_uri' => config('api.enigmaSecurities.baseUri')
        ]);

        $headers = [
            'Content-Type' => 'application/json',
        ];

        if ($this->authKey) {
            $headers['Authorization'] = 'Bearer ' . $this->authKey;
        }

        if(!empty($body))
            $body = json_encode($body, JSON_FORCE_OBJECT);
        else
            $body = '';

        try {
            $response = $client->request($method, $uri, [
                'headers' => $headers,
                'body' => $body,
                'debug' => true
            ]);
            if ($response->getStatusCode() !== 200) {
                Log::critical('Enigma Securities Request Failed', ['statusCode' => $response->getStatusCode(), 'message' => $response->getReasonPhrase()]);
                $response = null;
            }
        } catch (RequestException $e) {
            dump($e);
            if ($e->getCode() !== 404) {
                Log::critical('Enigma Securities Request Failed', ['statusCode' => $e->getCode(), 'message' => $e->getMessage()]);
            }

            $response = null;
        }
dd($response);
        return $response ? json_decode($response->getBody(), true) : [];
    }

    /**
     * Authentificate object
     *
     * @return mixed|null
     */
    protected function login() {
        $body = [
            'username' => config('api.enigmaSecurities.login'),
            'password' => config('api.enigmaSecurities.password')
        ];

        $response = $this->makeRequest('PUT', '/auth', $body);

        return $response['result'] ? $response['key'] : null;
    }

    /**
     * Logout from Enigma Securities system
     *
     * @return array|mixed
     */
    protected function logout() {
        return $this->makeRequest('DELETE', '/auth');
    }

    /**
     * @return array
     */
    public function getProduct():array {
        return $this->makeRequest('GET', '/product/');
    }

    /**
     * @param array $args
     * @return array
     */
    public function setQuote($args = []):array {
        $body = [];

        if (isset($args['side'])) {
            $body['side'] = $args['side']; // BUY | SELL
        }
        if (isset($args['product_id'])) {
            $body['product_id'] = $args['product_id']; // from /product endpoint
        }
        if (isset($args['quantity'])) {
            $body['quantity'] = $args['quantity']; // positive double
        }
        if (isset($args['nominal'])) {
            $body['nominal'] = $args['nominal']; // positive double
        }

        return $this->makeRequest('POST', '/quote/', $body);
    }

    /**
     * @param array $args
     * @return array
     */
    public function setTrade($args = []):array {
        $body = [];

        if (isset($args['type'])) {
            $body['type'] = $args['type']; // MKT | FOK | RFQ
        }
        if (isset($args['side'])) {
            $body['side'] = $args['side']; // BUY | SELL
        }
        if (isset($args['product_id'])) {
            $body['product_id'] = $args['product_id']; // from /product/ endpoint
        }
        if (isset($args['quantity'])) {
            $body['quantity'] = $args['quantity']; // base currency quantity, positive double
        }
        if (isset($args['price'])) {
            $body['price'] = $args['price']; // positive double required for FOK type
        }
        if (isset($args['nominal'])) {
            $body['nominal'] = $args['nominal']; // quote currency nominal , positive double, accepted for MKT type only instead of Quantity
        }
        if (isset($args['slippage'])) {
            $body['slippage'] = $args['slippage']; // non mandatory positive integer [0,20]
        }
        if (isset($args['quote_id'])) {
            $body['quote_id'] = $args['quote_id']; // mandatory for RFQ
        }

        return $this->makeRequest('POST', '/trade/', $body);
    }

    /**
     * @param array $args
     * @return array|mixed
     */
    public function getTrade($args = []) {
        $body = [];

        if (isset($args['items_per_page'])) {
            $body['items_per_page'] = $args['items_per_page']; // integer
        }
        if (isset($args['current_page'])) {
            $body['current_page'] = $args['current_page']; // integer
        }
        if (isset($args['sort'])) {
            $body['sort'] = $args['sort']; // trade_id desc
        }
        if (isset($args['start_date'])) {
            $body['start_date'] = $args['start_date']; // format YYYY-MM-DD
        }
        if (isset($args['end_date'])) {
            $body['end_date'] = $args['end_date']; // format YYYY-MM-DD
        }
        if (isset($args['trade_id'])) {
            $body['trade_id'] = $args['trade_id']; // string
        }
        if (isset($args['product_id'])) {
            $body['product_id'] = $args['product_id']; // string
        }
        if (isset($args['execution_type'])) {
            $body['execution_type'] = $args['execution_type']; // OTC | REST API
        }
        if (isset($args['already_batched'])) {
            $body['already_batched'] = $args['already_batched']; // -1 :all trades | 0: not batched only | 1: batched only
        }
        if (isset($args['status'])) {
            $body['status'] = $args['status']; // array e.g. [[0] => booked, [1] => validated, [2] => canceled]
        }

        $urlParams = '?';
        foreach ($body as $paramName => $paramValue) {
            if ($paramName === 'status' && is_array($paramValue)) {
                foreach ($paramValue as $key => $statusValue) {
                    $urlParams .= "{$paramName}[{$key}]={$statusValue}&";
                }
            } else {
                $urlParams .= "{$paramName}={$paramValue}&";
            }
        }
        $urlParams = trim($urlParams, '&');

        return $this->makeRequest('GET', "/trade/{$urlParams}");
    }

    /**
     * @return array|mixed
     */
    public function getBalance() {
        return $this->makeRequest('GET', '/balance/');
    }

    /**
     * @return array|mixed
     */
    public function getRiskLimit() {
        return $this->makeRequest('GET', '/risk_limit');
    }

    /**
     * @param array $args
     * @return array|mixed
     */
    public function getSettlement($args = []) {
        $body = [];

        if (isset($args['items_per_page'])) {
            $body['items_per_page'] = $args['items_per_page']; // integer
        }
        if (isset($args['current_page'])) {
            $body['current_page'] = $args['current_page']; // integer
        }
        if (isset($args['sort'])) {
            $body['sort'] = $args['sort']; // settlement_id desc
        }
        if (isset($args['start_date'])) {
            $body['start_date'] = $args['start_date']; // format YYYY-MM-DD
        }
        if (isset($args['end_date'])) {
            $body['end_date'] = $args['end_date']; // format YYYY-MM-DD
        }
        if (isset($args['side'])) {
            $body['side'] = $args['side']; // string
        }
        if (isset($args['status'])) {
            $body['status'] = $args['status']; // array e.g. [[0] => booked, [1] => validated, [2] => canceled, [3] => pending, [4] => settled]
        }
        if (isset($args['currency_list'])) {
            $body['currency_list'] = $args['currency_list']; // // array e.g. [[0] => EUR, [1] => BTC, [2] => USD]
        }

        $urlParams = '?';
        foreach ($body as $paramName => $paramValue) {
            if (($paramName === 'status' || $paramName === 'currency_list') && is_array($paramValue)) {
                foreach ($paramValue as $key => $statusValue) {
                    $urlParams .= "{$paramName}[{$key}]={$statusValue}&";
                }
            } else {
                $urlParams .= "{$paramName}={$paramValue}&";
            }
        }
        $urlParams = trim($urlParams, '&');

        return $this->makeRequest('GET', "/settlement/{$urlParams}");
    }

    /**
     * @param $args
     * @return array|mixed
     */
    public function getSettlementBatch($args) {
        $body = [];

        if (isset($args['sort'])) {
            $body['sort'] = $args['sort']; // settlement_batch_id desc
        }
        if (isset($args['product_id'])) {
            $body['product_id'] = $args['product_id']; // integer
        }
        if (isset($args['status'])) {
            $body['status'] = $args['status']; // array e.g. [[0] => booked, [1] => validated, [2] => canceled, [3] => pending, [4] => settled]
        }

        $urlParams = '?';
        foreach ($body as $paramName => $paramValue) {
            if ($paramName === 'status' && is_array($paramValue)) {
                foreach ($paramValue as $key => $statusValue) {
                    $urlParams .= "{$paramName}[{$key}]={$statusValue}&";
                }
            } else {
                $urlParams .= "{$paramName}={$paramValue}&";
            }
        }
        $urlParams = trim($urlParams, '&');

        return $this->makeRequest('GET', "/settlement_batch/{$urlParams}");
    }
}
