<?php
declare(strict_types=1);

namespace PHPAether\Core\HTTP;

use PHPAether\Enums\ResponseStatus;
use PHPAether\Interfaces\RenderableBodyInterface;

class Response
{

    public function __construct(
        public readonly ResponseStatus $status,
        public readonly RenderableBodyInterface|string $body,
        public readonly array $headers=[]
    )
    {

    }

    public function send(): never
    {
        // send headers
        $this->sendHeaders();

        // send status code
        http_response_code($this->status->value);

        // send body
        $body = $this->body;
        echo is_string($body) ? $body : $body->toString();

        exit();
    }

    public function sendHeaders(): void
    {
        foreach ($this->headers as $key => $value) {
            header("$key='$value'");
        }
    }

    public function __toString(): string
    {
        $this->send();
    }
}