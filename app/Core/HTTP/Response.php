<?php

namespace PHPAether\Core\HTTP;

use PHPAether\Enums\ResponseStatus;

class Response
{

    public function __construct(
        public readonly ResponseStatus $status,
        public readonly string $body,
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
        echo $this->body;

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