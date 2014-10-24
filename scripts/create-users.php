<?php

require dirname(__DIR__) . '/vendor/autoload.php';

$runner = new Runner([
  'username' => getenv('RS_USERNAME'),
  'apiKey'   => getenv('RS_API_KEY'),
  'authUrl'  => Enum::UK_AUTH,
  'region'   => Enum::REGION_LON,
]);

$roles = [
    10000256, // object-store:admin
    10000262, // cloudImages:creator
    10000267, // dnsaas:creator
    10000273, // queues:creator
    10000276, // autoscale:admin
    10000282, // monitoring:creator
    10000304, // big-data:creator
    10000310, // orchestration:creator
    173,      // block-storage:creator
    167,      // databases:creator
    179,      // servers:creator
];

$runner->createUsers([
  'prefix' => 'foo' . time(),
  'total'  => 2,
  'roles'  => $roles
]);
