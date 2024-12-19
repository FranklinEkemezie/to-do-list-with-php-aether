<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Entities\AbstractEntities;

interface Authenticable
{

    public function getUserId(): int|string;
}