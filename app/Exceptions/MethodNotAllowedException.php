<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Exceptions;

class MethodNotAllowedException extends \Exception
{

    private array $allowedMethods;

    public function __construct(string $message = "", array $allowedMethods=[], \Throwable $previous = null)
    {
        $this->allowedMethods = $allowedMethods;

        parent::__construct($message, 405, $previous);
    }

    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }
}