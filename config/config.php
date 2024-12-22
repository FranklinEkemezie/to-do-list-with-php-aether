<?php

use FranklinEkemezie\PHPAether\Services\AuthServices\SessionAuthService;

return [
    'AUTH_SERVICE'  => SessionAuthService::class,

    // Database Configuration
    'DB_CONFIG'     => [
        'driver'    => $_ENV['DB_DRIVER'],
        'host'      => $_ENV['DB_HOST'],
        'port'      => $_ENV['DB_PORT'],
        'database'  => $_ENV['DB_DATABASE'],
        'username'  => $_ENV['DB_USERNAME'],
        'password'  => $_ENV['DB_PASSWORD']   
    ]
];