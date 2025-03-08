<?php

namespace PHPAether\Utils\Collections;

class Dictionary
{

    protected array $dict;

    public function __construct(
        iterable $items=[]
    )
    {
        $this->dict = [...$items];
    }


}