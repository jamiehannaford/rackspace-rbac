<?php

require dirname(__DIR__) . '/vendor/autoload.php';

$runner = new Runner([
    'username' => getenv('RS_USERNAME'),
    'apiKey'   => getenv('RS_API_KEY'),
    'authUrl'  => Enum::UK_AUTH,
    'region'   => Enum::REGION_LON,
]);

$runner->deleteUsers('S');
