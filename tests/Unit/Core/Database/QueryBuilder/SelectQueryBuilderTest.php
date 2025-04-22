<?php


class SelectQueryBuilderTest extends BaseTestCase
{

    protected SelectQueryBuilder $queryBuilder;
    
    public function setUp(): void
    {
        parent::setUp();
        $this->queryBuilder = QueryBuilder::build(QueryType::SELECT);
    }

    #[Test]
    public function it_builds_full_query(): void
    {
        $this
            ->queryBuilder
            ->table('users|u')
            ->tableColumns('users', 'id', 'name', 'email')
            ->tableColumns('r', 'role_name')
            // COUNT aggregate function goes here
            ->join('roles|r', function (QueryBuilder $queryBuilder) {
                $queryBuilder->whereColumnIs('role_id', 'r.id');
            })
            ->joinLeft('posts|p', function (QueryBuilder $queryBuilder) {
                $queryBuilder->whereColumnIs('u.id', 'user_id');
            })
            ->whereColumnIs('status', 'active')
            ->whereColumnIsGreaterThan('age', 18)
            ->whereGroup(function (QueryBuilder $queryBuilder) {
                $queryBuilder
                    ->whereColumnIs('u.country', 'US')
                    ->whereColumnIs('u.country', 'CA', false); // false to join with OR
            })
            ->groupBy('id', 'name', 'email', 'role_name')
            ->having(function (QueryBuilder $queryBuidler) {
                $queryBuilder->whereColumnIs('post_count', 5);
            })
            ->orderByDesc('u.created_at')
            // you can also use `orderByAsc` if need be. Both accepts multiple values
            ->limit(20, 10)
        ;

        $actualQuery = <<<SQL
SELECT
    u.id, u.name, u.email, r.role_name, COUNT(p.id) AS post_count
FROM 
    users u
INNER JOIN 
    roles r ON u.role_id = r.id
LEFT JOIN 
    posts p ON u.id = p.user_id
WHERE 
    u.status = 'active'
    AND u.age > 18
    AND (u.country = 'US' OR u.country = 'CA')
GROUP BY 
    u.id, u.name, u.email, r.role_name
HAVING 
    post_count > 5
ORDER BY 
    u.created_at DESC
LIMIT 
    20 OFFSET 10;
SQL;

        return $this->assertSame(
            $actualQuery,
            (string) $this->queryBuilder
        );
    }
}
