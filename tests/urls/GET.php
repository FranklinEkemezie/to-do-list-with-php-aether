<?php

// List of test routes to simulate HTTP GET request

return [
    [
        'url'       => '/tests/home',
        'expected'  => [
            'router'    => [
                'route' => '/tests/home'
            ]
        ]
    ],
    [
        'url'       => '/tests/books?page=2&sort=name',
        'expected'  => [
            'request'   => [
                'query' => [
                    'page'  => '2',
                    'sort'  => 'name'
                ]
            ],
            'router'    => [
                'route' => '/tests/books'
            ]
        ]
    ],
    [
        'url'       => '/tests/books/2',
        'expected'  => [
            'router'    => [
                'route' => '/tests/books/:id',
                'params'    => [
                    'id'    => 2
                ]
            ]
        ]
    ]
];