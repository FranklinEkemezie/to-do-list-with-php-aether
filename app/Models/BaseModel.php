<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Models;

use FranklinEkemezie\PHPAether\Core\Database;
use FranklinEkemezie\PHPAether\Entities\AbstractEntities\BaseEntity;
use FranklinEkemezie\PHPAether\Utils\Dictionary;

abstract class BaseModel
{

    public function __construct(
        protected Database $database
    )
    {
        
    }

    abstract public static function buildEntityFromDict(Dictionary $dict): BaseEntity;



}