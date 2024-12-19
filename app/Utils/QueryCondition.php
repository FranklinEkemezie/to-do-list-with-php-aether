<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Utils;

class QueryCondition
{

    public function __construct()
    {

    }

    public function not(string $condition)
    {

    }
}


// x AND (y OR NOT z)
[
    'x',
    [
        true,
        'y',
        [false, 'z']
    ]
];

// x AND NOT (y OR z)
[
    'x',
    [
        false,
        'y',
        'z'
    ]
];

// x AND (y OR NOT (x AND z))
// x AND (y OR NOT (x AND z))
[
    true,   // join this group with AND
    false,  // do not invert this group

    // Actual conditions
    'x',
    [
        false,  // join this group with OR
        false,  // do not invert this group

        'y',
        [
            true,   // join this group with AND
            true,   // invert this group

            'x',
            'z'
        ]
    ]

];

function getConditionsString(array $conditions): string
{
    if (empty($condition))
        return 'false';

    return '';
}

