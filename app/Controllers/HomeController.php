<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Controllers;

use FranklinEkemezie\PHPAether\Core\Database;
use FranklinEkemezie\PHPAether\Core\Response;
use FranklinEkemezie\PHPAether\Utils\Collection;
use FranklinEkemezie\PHPAether\Utils\Dictionary;
use FranklinEkemezie\PHPAether\Utils\QueryBuilder\QueryBuilder;

class HomeController extends BaseController
{


    public function index(): Response
    {

        $x = (
            new Collection(
                ...$this->database->executeSQLQuery(
                    QueryBuilder::build('select')
                        ->table('book')
                        ->columns()
                )
            )
        )->map(fn(array $item): Dictionary => new Dictionary($item));

        echo json_encode($x);
    
        return new Response(body: "u");
    }
}
