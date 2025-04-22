<?php

namespace PHPAether\Enums;

enum QueryBuilderType: string
{

    case SELECT = 'SELECT';
    case INSERT = 'INSERT';
    case UPDATE = 'UPDATE';
    case DELETE = 'DELETE';
}