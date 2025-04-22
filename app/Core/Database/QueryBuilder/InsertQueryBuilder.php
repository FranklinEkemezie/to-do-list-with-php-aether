<?php

namespace PHPAether\Core\Database\QueryBuilder;

use PHPAether\Enums\QueryBuilderType;

class InsertQueryBuilder extends QueryBuilder
{

    public const QUERY_TYPE = QueryBuilderType::SELECT;

    public function sql(): string
    {
        return "";
    }
}