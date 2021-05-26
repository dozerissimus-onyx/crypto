<?php


namespace App\Service;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

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

    /**
     * @param string $method
     * @param string $uri
     * @return mixed
     */
    abstract protected function makeRequest(string $method, string $uri);

    /**
     * @param string $method
     * @param string $uri
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendRequest(string $method, string $uri) {
        try {
            $response = $this->client->request($method, $uri, [
                'headers' => $this->headers,
                'body' => $this->body,
            ]);
            if ($response->getStatusCode() !== 200 && $response->getStatusCode() !== 201) {
                // Some actions
            }
            dump($response->getStatusCode());
            return $response;
        } catch (RequestException $e) {
            Log::critical(get_class($this) . ' Request Failed', ['message' => $e->getMessage()]);
        }
    }
}
