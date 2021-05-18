<?php


namespace App\Service;


use GuzzleHttp\Client;

abstract class ApiWrapper
{
    /**
     * @var Client $client
     */
    protected $client;

    /**
     * @var array $headers
     */
    protected $headers;

    /**
     * @var array $body
     */
    protected $body;

    /**
     * @var array $params
     */
    protected $params;



    /**
     * @param array $params
     */
    public function setParams(array $params) {
        $this->params = $params;
    }

    abstract protected function makeRequest($method, $uri);

    protected function sendRequest($method, $uri) {
        try {
            $response = $this->client->request($method, $uri, [
                'headers' => $this->headers,
                'json' => $this->body,
                'debug' => true
            ]);
            if ($response->getStatusCode() != 200 && $response->getStatusCode() != 201) {
                dd($response->getStatusCode());
            }
        } catch (GuzzleHttp\Exception\GuzzleException $e) {
            error_log($e);
        }
        return $response;
    }
}
