<?php
return [
    'hosts' => [
        [
            'host'   => parse_url(env('ELASTICSEARCH_HOST', 'localhost:9200'), PHP_URL_HOST),
            'port'   => parse_url(env('ELASTICSEARCH_HOST', 'localhost:9200'), PHP_URL_PORT) ?? env('ELASTICSEARCH_PORT', 9200),
            'scheme' => env('ELASTICSEARCH_SCHEME', 'http'),
            'user'   => env('ELASTICSEARCH_USERNAME', 'elastic'),
            'pass'   => env('ELASTICSEARCH_PASSWORD', 'lRWnYzak'),
            'retries' => 3,
            'analyzer' => 'french'
        ]
    ],
];
