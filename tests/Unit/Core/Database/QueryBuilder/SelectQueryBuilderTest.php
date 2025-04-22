<?php

namespace Core\Database\QueryBuilder;

use PHPAether\Core\Database\QueryBuilder\QueryBuilder;
use PHPAether\Core\Database\QueryBuilder\SelectQueryBuilder;
use PHPAether\Exceptions\DatabaseExceptions\QueryBuilderException;
use PHPAether\Tests\BaseTestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SelectQueryBuilderTest extends BaseTestCase
{

    protected SelectQueryBuilder $queryBuilder;

    /**
     * @throws QueryBuilderException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->queryBuilder = QueryBuilder::select('users', 'sales', 'carts');
    }

    /**
     * @throws QueryBuilderException
     */
    #[Test]
    public function it_builds_simple_query(): void
    {
        $this->assertSame(
            (string) $this->queryBuilder->columns('id', 'name', 'email'),
            "SELECT id, name, email FROM users, sales, carts;"
        );
    }

    /**
     * @throws QueryBuilderException
     */
    #[Test]
    public function it_builds_query_with_alias_columns(): void
    {
        $this->assertSame(
            (string) $this->queryBuilder->columns('id|user_id', 'name|username', 'email'),
            "SELECT id AS user_id, name AS username, email FROM users, sales, carts;"
        );
    }

    #[Test]
    public function it_prevents_query_with_invalid_column_name(): void
    {
        $this->expectException(QueryBuilderException::class);
        $this->queryBuilder->columns('id|user_id', 'first name|first name', 'email');
    }

    /**
     * @throws QueryBuilderException
     */
    #[Test]
    public function it_builds_query_with_simple_where_clause(): void
    {
        $this->queryBuilder->columns('id', 'username', 'email');
        $this->assertSame(
            (string) $this->queryBuilder->where("email = 'some@example.com' OR carts.size >= 10"),
            "SELECT id, username, email FROM users, sales, carts WHERE email = 'some@example.com' OR carts.size >= 10;"
        );

        $this->assertSame(
            (string) $this->queryBuilder->whereColumnIs('status', 4),
            "SELECT id, username, email FROM users, sales, carts WHERE email = 'some@example.com' OR carts.szie >= 10 AND status = '4';"
        );
    }

    /**
     * @throws QueryBuilderException
     */
    #[Test]
    public function it_builds_query_with_complex_where_clause(): void
    {
        $this->queryBuilder->columns('id|user_id', 'name|username', 'email');

        $this->assertSame(
            (string) $this->queryBuilder->whereColumnsAre([
                'age'   => 45,
                'salary'=> 2005
            ]),
            ""
        );

        $this->assertSame(
            (string) $this->queryBuilder->whereColumnsCanBe([

            ]),
            ""
        );
    }

    #[Test]
    public function it_builds_full_sql_query(): void
    {
        $query = <<<SQL
SELECT
    u.id, u.name, u.email, r.role_name, COUNT(p.id) AS post_count
FROM
    users u
INNER JOIN
    roles r ON u.role_id = r.id
LEFT JOIN
    posts p ON u.id = p.user_id
WHERE
    u.status = 'active' AND
    u.age > 18 AND
    (u.country = 'US' OR u.country = 'CA')
GROUP BY
    u.id, u.name, u.email, r.role_name
HAVING
    post_count > 5
ORDER BY
    u.created_at DESC
LIMIT
    20 OFFSET 10
SQL;
    }
}
