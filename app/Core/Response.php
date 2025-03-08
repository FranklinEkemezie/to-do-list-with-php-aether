<?php

namespace PHPAether\Core;

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
        http_send_status($this->status->value);

        // send body
        echo $this->body;

        exit();
    }

    public function sendHeaders(): void
    {
        array_walk($this->headers, function ($value, $key) {
            header("$key='$value'");
        });
    }

    public function __toString(): string
    {
        $this->send();
    }
}