<?php


class InsertQueryBuilder extends BaseTestCase
{

    protected InsertQueryBuilder $queryBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->queryBuilder = QueryBuilder::build(QueryType::INSERT);
    }

    #[Test]
    public function it_builds_full_query(): void
    {

        $bobData = [
            'email'     => 'bob@example.com',
            'username'  => 'bob',
            'password'  => 'pwd2'
        ];

        $this
            ->queryBuilder
            ->columns('username', 'email', 'password')
            ->values(
                ['alice', 'alice@example.com', 'pwd1'],
                $bobData
            )
        ;

        $actualQuery = <<<SQL
INSERT INTO users (username, email, password)
VALUES
    ('alice', 'alice@example.com', 'pwd1'),
    ('bob', 'bob@example.com', 'pwd2');
SQL;

        return $this->assertSame(
            $actualQuery,
            (string) $this->queryBuilder
        );
    }
}
