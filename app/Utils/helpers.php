<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Utils;

/**
 * Dump the values
 * @param mixed[] $values
 * @return void
 */
function dump(mixed ...$values): void
{
    var_dump(...$values);
}

/**
 * Dump the values in a pretty format.
 * @param mixed[] $values
 * @return void
 */
function dumpPretty(mixed ...$values): void
{
    echo '<pre>';
    var_dump(...$values);
    echo '</pre>';
}

/**
 * Redirect to a given URL
 * @param string $url
 * @return void
 */
function redirect(string $url): void
{
    header("Location: $url");
    exit;
}