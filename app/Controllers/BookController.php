<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Controllers;

use FranklinEkemezie\PHPAether\Core\Response;
use FranklinEkemezie\PHPAether\Entities\Book;
use FranklinEkemezie\PHPAether\Models\BookModel;

use function FranklinEkemezie\PHPAether\Utils\dumpPretty;

class BookController extends BaseController
{

    public function getBooks(): Response
    {
        $author = $this->request->GET->get('author');
        $bookModel = new BookModel($this->database);

        $books = $author === null ? 
            $bookModel->getBooks() :
            $bookModel->getBooksByAuthor($author)
        ;

        return new Response(
            body: json_encode($books)
        );
    }

    public function addBook(): Response
    {

        $formData = $this->request->POST;

        $isbn   = (string) $formData->get('isbn');
        $title  = $formData->get('title');
        $author = $formData->get('author');
        $stock  = (int) $formData->get('stock');
        $price  = (float) $formData->get('price');

        if (! $isbn || ! $title || ! $author || ! $stock || ! $price) {
            return new Response(403, body: "Invalid form data");
        }

        $book = new Book($isbn, $title, $author, $stock, $price);
        $bookModel = new BookModel($this->database);
        $insertId = $bookModel->addBook($book);

        return new Response(body: "Inserted book with ID: $insertId");
    }

    public function getBook(int $isbn): Response
    {
        $bookModel = new BookModel($this->database);
        $book = $bookModel->getBook((string) $isbn);

        return new Response(body: json_encode($book));
    }
}