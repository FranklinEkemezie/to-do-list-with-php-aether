<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Entities\AbstractEntities;

use FranklinEkemezie\PHPAether\Exceptions\UndefinedException;

abstract class BaseEntity implements \JsonSerializable
{


    public function __get(string $name): mixed
    {
        if (! property_exists($this, $name)) {
            throw new UndefinedException('Undefined property: ' . __CLASS__ . '::$' . $name);
        }

        return $this->$name;
    }

    public function jsonSerialize(): array
    {
        $res = [];
        foreach($this as $prop => $value) {
            $res[$prop] = $value;
        }

        return $res;
    }

}