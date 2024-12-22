<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Controllers;

use FranklinEkemezie\PHPAether\Core\Database;
use FranklinEkemezie\PHPAether\Core\Response;
use FranklinEkemezie\PHPAether\Utils\QueryBuilder\QueryBuilder;

class HomeController extends BaseController
{

    public function index(): Response
    {

        $dbConfig = [
            'driver'    => $_ENV['DB_DRIVER'],
            'host'      => $_ENV['DB_HOST'],
            'database'  => 'bookstore',
            'username'  => $_ENV['DB_USERNAME'],
            'password'  => $_ENV['DB_PASSWORD']
        ];

        $db = new Database($dbConfig);

        $deleteQuery = QueryBuilder::build('delete')
            ->table('users')
            ->whereColumnsCanBeLike([
                'name'  => 'OG%',
                'email' => '%@gmail.com'
            ])
        ;

        $body = (string) $deleteQuery;
    
        return new Response(body: $body);
    }
}
