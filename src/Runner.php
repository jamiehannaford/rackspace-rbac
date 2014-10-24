<?php

use GuzzleHttp\Client;

class Runner
{
    const JSON = 'application/json';

    private $client;
    private $config;

    public function __construct(array $config)
    {
        $this->validateKey($config, 'username');
        $this->validateKey($config, 'authUrl');

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

        $this->config = $config;
    }

    private function validateKey(array $array, $key)
    {
        if (empty($array[$key])) {
            throw new InvalidArgumentException(sprintf('A valid %s is required, none provided', $key));
        }
    }

    private function getPasswordJson()
    {
        return sprintf(
            '{"auth":{"passwordCredentials":{"username":"%s","password":"%s"}}}',
            $this->config['username'],
            $this->config['password']
        );
    }

    private function getApiKeyJson()
    {
        return sprintf(
            '{"auth":{"RAX-KSKEY:apiKeyCredentials":{"username":"%s","apiKey":"%s"}}}',
            $this->config['username'],
            $this->config['apiKey']
        );
    }

    private function getUserJson($username, $password)
    {
        return sprintf('{"user":{"username":"%s","OS-KSADM:password":"%s","enabled":true}}', $username, $password);
    }

    private function authenticate()
    {
        $json = !empty($this->config['password']) ? $this->getPasswordJson() : $this->getApiKeyJson();

        $response = $this->client->post('tokens', ['body' => $json])->json();

        if (!isset($response['access']['token']['id'])) {
            throw new RuntimeException('Cannot extract token ID from Guzzle response');
        }

        $this->client->setDefaultOption('headers/X-Auth-Token', $response['access']['token']['id']);
    }

    private function makePassword($string)
    {
        return sha1(md5(rand(1,1000) . $string . rand(1, 1000) . microtime()));
    }

    public function listRoles()
    {
        $this->authenticate();

        $response = $this->client->get('OS-KSADM/roles?limit=100')->json();

        if (isset($response['roles'])) {
            foreach ($response['roles'] as $role) {
                printf("%s %s\n", $role['id'], $role['name']);
            }
        }
    }

    public function createUsers(array $opts)
    {
        $this->validateKey($opts, 'total');
        $this->authenticate();

        $users = [];

        for ($i = 1; $i <= $opts['total']; $i++) {
            $username = (!empty($opts['prefix']) ? $opts['prefix'] : 'user') . '_' . $i;
            $password = $this->makePassword($username);

            $response = $this->client->post('users', ['body' => $this->getUserJson($username, $password)]);
            $json = $response->json();

            if ($response->getStatusCode() == 201 && !empty($json['user']['id'])) {
                $users[] = $json['user']['id'];
                printf("username=%s password=%s\n", $username, $password);
            }
        }

        printf("\n\nAdding roles\n\n");

        foreach ($users as $userId) {
            foreach ($opts['roles'] as $roleId) {
                $response = $this->client->put(sprintf('users/%s/roles/OS-KSADM/%s', $userId, $roleId), ['future' => true]);
                $response->then(
                    function() use ($userId, $roleId, &$complete) {
                        printf("Added role %s to %s\n", $roleId, $userId);
                    },
                    function($error) use ($userId, $roleId, &$complete) {
                        printf("Failed adding role %s to %s: %s\n", $roleId, $userId, $error->getMessage());
                    }
                );
            }
        }

        return true;
    }

    public function deleteUsers($prefix)
    {
        if (!$prefix) {
            throw new InvalidArgumentException("A prefix must be provided");
        }

        $this->authenticate();

        $response = $this->client->get('users?limit=1000')->json();

        if (empty($response['users'])) {
            return false;
        }

        foreach ($response['users'] as $user) {
            $username = $user['username'];
            if (strpos($username, $prefix) === 0) {
                $response = $this->client->delete(sprintf('users/%s', $user['id']), ['future' => true]);
                $response->then(
                    function () use ($username) {
                        printf("Successfully deleted user %s\n", $username);
                    },
                    function ($error) use ($username) {
                        printf("Failed to delete user %s: %s\n", $username, $error->getMessage());
                    }
                );
            }
        }

        return true;
    }
}
