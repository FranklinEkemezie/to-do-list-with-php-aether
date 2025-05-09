<?php
declare(strict_types=1);

namespace PHPAether\Interfaces;

abstract class RenderableBodyInterface
{

    public abstract function toString(): string;

    public function __toString(): string
    {
        return $this->toString();
    }
}