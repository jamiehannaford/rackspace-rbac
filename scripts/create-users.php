<?php

require dirname(__DIR__) . 'vendor/autoload.php';

use Endpoint, Region;

$runner = new Runner([
  'username' => getenv('OS_USERNAME'),
  'apiKey'   => getenv('OS_API_KEY'),
  'authUrl'  => Endpoint::UK,
  'region'   => Region::LON,
]);

$runner->createUsers([
  'prefix' => 'hack_event',
  'total'  => 50,
  'roles'  => []
]);
