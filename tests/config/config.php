<?php

require_once dirname(__DIR__, 2) . "/config/constants.php";

return [

    /**
     * Database Configuration
     */
    'DB'    => [

    ],

    /*
     * Authentication Configuration
     */
    'AUTH'  => [

    ],

    /*
     * View Configuration
     */
    'VIEW'  => [
        'DEFAULT_VIEW_DIR'  => TESTS_DIR . "/views"
    ]
];