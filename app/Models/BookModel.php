<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Models;

use FranklinEkemezie\PHPAether\Core\Database;
use FranklinEkemezie\PHPAether\Entities\Book;
use FranklinEkemezie\PHPAether\Exceptions\DatabaseException;
use FranklinEkemezie\PHPAether\Utils\Collection;
use FranklinEkemezie\PHPAether\Utils\QueryBuilder\InsertQueryBuilder;
use FranklinEkemezie\PHPAether\Utils\QueryBuilder\QueryBuilder;

use function FranklinEkemezie\PHPAether\Utils\dumpPretty;

class BookModel extends BaseModel
{

    public function getBooks(): Collection
    {
        return new Collection(
            ...$this->database->executeSQLQuery(
                QueryBuilder::build('select')->table('book')->columns()
            )
        );
    }

    public function getBooksByAuthor(string $author): Collection
    {
        return new Collection(
            ...$this->database->executeSQLQuery(
                QueryBuilder::build('select')
                ->table('book')
                ->columns()
                ->whereColumnIs('author', $author)
            )
        );

    }

    public function getBook(string $isbn): ?Book
    {
        $bookDetails = ($this->database->executeSQLQuery(
            QueryBuilder::build('select')
            ->table('book')->columns()
            ->whereColumnIs('isbn', $isbn)
        ))[0] ?? null;

        if (empty($bookDetails)) return null;
        
        return new Book(
            $bookDetails['isbn'],
            $bookDetails['title'],
            $bookDetails['author'],
            $bookDetails['stock'],
            $bookDetails['price']
        );
    }

    public function addBook(Book $book): ?int
    {
        /**
         * @var InsertQueryBuilder $insertQuery
         */
        $insertQuery = QueryBuilder::build('insert')
        ->table('book')
        ->columns('isbn', 'title', 'author', 'stock', 'price')
        ->values(
            $book->isbn,
            $book->title,
            $book->author,
            $book->stock,
            $book->price
        );

        /** @var Database|\PDO $conn */
        $conn = $this->database;
        $conn->executeSQLQuery($insertQuery);

        return (int) $conn->lastInsertId();
    }
}