<?php

namespace PHPAether\Tests\TestControllers;

use PHPAether\Controllers\Controller;
use PHPAether\Core\HTTP\Request;
use PHPAether\Core\HTTP\Response;
use PHPAether\Enums\ResponseStatus;
use PHPAether\Tests\views\pages\Books;
use PHPAether\Tests\views\pages\Book;

class BookController extends Controller
{

    public function index(): Response
    {
        $books = [
            ['id' => 3],
            ['id' => 8],
            ['id' => 13]
        ];

        $props = ['books' => $books];
        return new Response(ResponseStatus::OK, new Books($props));
    }

    public function getBook(Request $request): Response
    {
        $bookId = $request->getData('id');
        $props = ['id' => $bookId];
        return new Response(ResponseStatus::OK, new Book($props));
    }
}