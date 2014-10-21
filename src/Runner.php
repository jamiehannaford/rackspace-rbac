<?php

use GuzzleHttp\Client;

class Runner
{
    const JSON = 'application/json';

    private $client;

    public function __construct(array $config)
    {
        if (empty($config['username'])) {
            throw new InvalidArgumentException('A valid username is required, none provided');
        }

        if (empty($config['authUrl'])) {
            throw new InvalidArgumentException('A valid authUrl is required, none provided');
        }

        if (empty($config['apiKey']) && empty($config['password'])) {
            throw new InvalidArgumentException('A valid apiKey or password is required, none provided');
        }

        $this->client = new Client([
            'base_url' => $config['authUrl'],
            'defaults' => [
                'headers' => [
                    'Content-Type' => self::JSON,
                    'Accept'       => self::JSON,
                ]
            ]
        ]);

        if (!empty($config['apiKey'])) {
            $this->authenticateWithKey($config['username'], $config['apiKey']);
        } else {
            $this->authenticateWithPassword($config['username'], $config['password']);
        }
    }

    private function authenticateWithPassword($username, $password)
    {
        $json = sprintf('{"auth":{"passwordCredentials":{"username":"%s","password":"%s"}}}', $username, $password);
        $this->authenticate($json);
    }

    private function authenticateWithKey($username, $key)
    {
        $json = sprintf('{"auth":{"RAX-KSKEY:apiKeyCredentials":{"username":"%s","apiKey":"%s"}}}', $username, $key);
        $this->authenticate($json);
    }

    private function authenticate($json)
    {
        $response = $this->client->post('tokens', ['body' => $json])->json();

        if (!isset($response['access']['token']['id'])) {
            throw new RuntimeException('Cannot extract token ID from Guzzle response');
        }

        $this->client->setDefaultOption('header/X-Auth-Option', $response['access']['token']['id']);
    }
}