<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

return [
    'app' => [
        'hash'      => 'cYi1sVLQ5wzm6gG0Ne0o8rpgkC3K8GzWVoU8tMjpJwDWm4JTFhDNn34JxEUQnGsT', // generated using Helper::generateRandomString
        'secret'    => 'U0hiqmizT7repIgy3wX1uJv6R3T8YtskNcZmF7ClH2ajBtE4nF8WXQGNAw5b3VVe', // generated using Helper::generateRandomString
        'debug'     => true,
        'log'       => true
    ],
    'db' => [
        'dsn'       => "mysql:host=localhost;dbname=dbname",
        'dbu'       => "dbusername",
        'dbp'       => "dbpassword"
    ],
    'mail' => [
        'host'      => 'mail.domain.com',
        'user'      => 'raiza@domain.com',
        'pass'      => 'password',
    'name'          => 'Raiza Rhamdan'
    ]
];
