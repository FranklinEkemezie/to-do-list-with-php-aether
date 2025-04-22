<?php


class UpdateQueryBuilderTest extends BaseTestCase
{

    protected QueryBuilder $queryBuilder;

    public function setUp(): void
    {
        $this>queryBuilder = QueryBuilder::build(QueryType::UPDATE, 'users');
    }

    #[Test]
    public function it_builds_full_query(): void
    {

        $this
            ->queryBuilder
            ->update([
                'name'      => 'John Doe',
                'email'     => 'john.doe@example.com',
                'password'  => 'new_hashed_password',
                'updated_at'=> 'CURRENT_TIMESTAMP',
                'is_active' => true
            ])
            ->whereColumnIs('id', 1)
            ->whereColumnIs('deleted_at', NULL)
        ;

        $actualQuery = <<<SQL
UPDATE users
SET 
    name = 'John Doe',
    email = 'john.doe@example.com',
    password = 'new_hashed_password',
    updated_at = CURRENT_TIMESTAMP,
    is_active = TRUE
WHERE 
    id = 1
    AND deleted_at IS NULL;
SQL;

        return $this->assertSame(
            $actualQuery,
            (string) $this->queryBuilder
        );
    }
}
