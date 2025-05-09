<?php
declare(strict_types=1);

require_once __DIR__ . '/constants.php';

return [
    'DB'    => require CONFIG_DIR . '/database/database.php',
    'AUTH'  => require CONFIG_DIR . '/auth/auth.php',
    'VIEW'  => require CONFIG_DIR . '/view/view.php'
];