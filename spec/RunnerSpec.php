<?php

namespace spec;

use GuzzleHttp\Client;
use GuzzleHttp\Message\FutureResponse;
use GuzzleHttp\Message\Response;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RunnerSpec extends ObjectBehavior
{
    const TOKEN_ID = '8e044b4a-5925-11e4-aa15-123b93f75cba';

    private $keyCreds = [
        'username' => 'foo',
        'apiKey'   => 'bar',
        'authUrl'  => 'baz'
    ];

    private $passCreds = [
        'username' => 'foo',
        'password' => 'bar',
        'authUrl'  => 'baz'
    ];

    private $authArray = [
        'access' => [
            'token' => [
                'id' => self::TOKEN_ID
            ]
        ]
    ];

    public function let(Client $mockClient)
    {
        $this->beConstructedWith($this->keyCreds, $mockClient);
    }

    private function makeException($string)
    {
        return new \InvalidArgumentException(sprintf('A valid %s is required, none provided', $string));
    }

    function it_throws_exception_when_required_config_vars_are_not_provided()
    {
        $e = $this->makeException('username');
        $this->shouldThrow($e)->during__construct([]);

        $e = $this->makeException('authUrl');
        $this->shouldThrow($e)->during__construct(['username' => 'foo', 'apiKey' => 'bar']);

        $e = $this->makeException('apiKey or password');
        $this->shouldThrow($e)->during__construct(['username' => 'foo', 'authUrl' => 'baz']);
    }

    function it_throws_exception_when_creating_users_without_required_opts(Response $response)
    {
        // Test our method
        $e = $this->makeException('total');
        $this->shouldThrow($e)->duringCreateUsers([]);
    }

    function it_authenticates_with_passwords(Client $mockClient, Response $response, FutureResponse $futureResponse)
    {
        $response->json()->willReturn($this->authArray);

        $json = sprintf('{"auth":{"passwordCredentials":{"username":"foo","password":"bar"}}}');
        $mockClient->setDefaultOption('headers/X-Auth-Token', self::TOKEN_ID)->shouldBeCalled();
        $mockClient->post('tokens', ['body' => $json])->shouldBeCalled()->willReturn($response);

        // We're not testing this, so mock with any arg
        $mockClient->post('users', Argument::any())->willReturn($futureResponse);

        $this->beConstructedWith($this->passCreds, $mockClient);

        $this->createUsers(['total' => 10]);
    }

    function it_authenticates_with_api_keys(Client $mockClient, Response $response, FutureResponse $futureResponse)
    {
        // Ensure the X-Auth-Token header is set
        $mockClient->setDefaultOption('headers/X-Auth-Token', self::TOKEN_ID)->shouldBeCalled();

        // Ensure a response is returned when POSTing
        $response->json()->willReturn($this->authArray);
        $json = sprintf('{"auth":{"RAX-KSKEY:apiKeyCredentials":{"username":"foo","apiKey":"bar"}}}');
        $mockClient->post('tokens', ['body' => $json])->shouldBeCalled()->willReturn($response);

        // We're not testing this, so mock with any arg
        $mockClient->post('users', Argument::any())->willReturn($futureResponse);

        // Inject deps
        $this->beConstructedWith($this->keyCreds, $mockClient);

        // Call our method
        $this->createUsers(['total' => 1]);
    }

    function it_posts_users_n_times(Client $mockClient, Response $response, FutureResponse $futureResponse)
    {
        // Ensure the X-Auth-Token header is set
        $mockClient->setDefaultOption('headers/X-Auth-Token', self::TOKEN_ID)->shouldBeCalled();

        // Ensure a response is returned when POSTing
        $response->json()->willReturn($this->authArray);
        $json = sprintf('{"auth":{"RAX-KSKEY:apiKeyCredentials":{"username":"foo","apiKey":"bar"}}}');
        $mockClient->post('tokens', ['body' => $json])->shouldBeCalled()->willReturn($response);

        $total = 10;

        // Set up POST expectations
        for ($i = 1; $i <= $total; $i++) {
            $mockClient->post('users', [
                'body' => '{"user":{"username":"user_'.$i.'","enabled":true}}',
                'future' => true,
            ])->shouldBeCalled()->willReturn($futureResponse);
        }

        // Inject deps
        $this->beConstructedWith($this->keyCreds, $mockClient);

        // Call our method
        $this->createUsers(['total' => $total]);
    }
}
