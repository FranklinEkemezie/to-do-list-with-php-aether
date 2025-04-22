<?php


class DeleteQueryBuilderTest extends BaseTestCase
{

    protected DeleteQueryBuilder $queryBuilder;

    public function setUp(): void
    {

        $this->queryBuilder = QueryBuilder::build(QueryType::DELETE, 'users');
    }

    #[Test]
    public function it_builds_full_query(): void
    {
        $this
            ->whereColumnIs('id', 1)
            ->whereColumnIs('is_active', false)
            ->whereColumnIs('deleted_at', NULL)
        ;

        $actualQuery = <<<SQL
DELETE FROM users
WHERE id = 1
    AND is_active = FALSE
    AND deleted_at IS NULL;
SQL;

        return $this->assertSame(
            $actualQuery,
            (string) $this->queryBuilder
        );
    }
}
