<?php

namespace spec;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RunnerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Runner');
    }

    function let()
    {
        $this->beConstructedWith([
            'username' => 'foo',
            'apiKey'   => 'bar',
            'authUrl'  => 'baz'
        ]);
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

    function it_throws_exception_when_creating_users_without_required_opts()
    {
        $e = $this->makeException('total');
        $this->shouldThrow($e)->duringCreateUsers([]);
    }
}
