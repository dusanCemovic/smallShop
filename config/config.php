<?php
return [
    'db' => [
        'dsn' => 'mysql:host=127.0.0.1;dbname=creatim;charset=utf8mb4',
        'user' => 'dusan',
        'pass' => 'root',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ],
    ],
    'app' => ['base_url' => '/'],
    'sms' => ['primary' => 'provider_a', 'providers' => ['provider_a' => [], 'provider_b' => []]]
];

