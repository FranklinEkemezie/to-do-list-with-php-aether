<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Controllers;

use FranklinEkemezie\PHPAether\Core\Response;

class PostController extends BaseController
{

    public function edit(string $id): Response
    {
        echo 'Hello from (edit) Post controller <br/>';
        echo "Post ID is: $id <br/>";

        echo '<br/>';
        return new Response;
    }
}