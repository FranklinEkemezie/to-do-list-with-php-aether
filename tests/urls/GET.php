<?php

return [
    [
        'url'       => '/users?page=2&sort=name',
        'expected'  => [
            'action'    => ['Users', 'getUsers']
        ]
    ]
];