<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Entities;

use FranklinEkemezie\PHPAether\Entities\AbstractEntities\BaseEntity;

class Book extends BaseEntity
{
    
    public function __construct(
        protected string $isbn,
        protected string $title,
        protected string $author,
        protected int $stock,
        protected float $price
    )
    {

    }

}