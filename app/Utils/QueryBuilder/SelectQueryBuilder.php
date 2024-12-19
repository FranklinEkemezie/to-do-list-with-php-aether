<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Utils\QueryBuilder;

use FranklinEkemezie\PHPAether\Exceptions\QueryBuilderException;

class SelectQueryBuilder extends QueryBuilder
{

    private array|string $columns;
    private array $joins;
    protected array $whereCondition;
    private array $groupByColumns;
    private array $havingCondition;
    private array $orderByColumns;
    private array $selectLimit;

    protected function __construct() {}

    public function getType(): string
    {
        return 'select';
    }

    /**
     * Set the columns to retrieve
     * 
     * @param string|array[] $columns Argument list of columns.
     * Use `string` (e.g. `username`) for column names; and
     * two-item array (e.g. `['user.id', 'user']`) for column names with aliases.
     * @throws \FranklinEkemezie\PHPAether\Exceptions\QueryBuilderException when the array is not is in the required format
     * @return \FranklinEkemezie\PHPAether\Utils\QueryBuilder\SelectQueryBuilder
     */
    public function columns(string|array ...$columns): self
    {
        if (
            empty($columns) ||                                  # if no argument is suppled, or
            isset($this->columns) && $this->columns === '*'     # the property is set to '*' already
        ) {
            $this->columns = '*';
            return $this;
        }

        // Validate the input:
        $columns = array_values($columns);
        foreach($columns as $column) {
            // Column array must be in the form: ['col_name', 'Column Alias']
            if (
                is_string($column) ||
                is_array($column) &&            # if the column is array
                count($column) === 2 &&         # and has two items,
                ([$colName, $alias] = $column) &&      # destructure items here, nothing here: always returns true
                is_string($colName) && is_string($alias)
            ) continue;

            throw new QueryBuilderException('Invalid column');
        }

        if (! isset($this->columns)) $this->columns = [];

        array_push($this->columns, ...$columns);

        return $this;
    }

    private function setJoin(string $joinType, string $table, string $condition): self
    {
        $acceptJoinTypes = ['inner', 'left', 'right', 'full'];
        if (! in_array($joinType = strtolower($joinType), $acceptJoinTypes))
            throw new QueryBuilderException("Invalid join type: $joinType");

        // Modify the conditions to be compatible with query condition standard
        $conditions = [[false, $condition]];

        $this->joins[] = [
            'type'      => $joinType,
            'table'     => $table,
            'conditions'=> $conditions
        ];
        return $this;
    }

    // JOIN methods

    public function innerJoin(string $table, string $condition): self
    {
        return $this->setJoin('inner', $table, $condition);
    }

    public function join(string $table, string $condition): self
    {
        return $this->innerJoin($table, $condition);
    }

    public function leftJoin(string $table, string $condition): self
    {
        return $this->setJoin('left', $table, $condition);
    }

    public function rightJoin(string $table, string $condition): self
    {
        return $this->setJoin('right', $table, $condition);
    }

    public function fullJoin(string $table, string $condition): self
    {
        return $this->setJoin('full', $table, $condition);
    }

    public function outerJoin(string $table, string $condition): self
    {
        return $this->rightJoin($table, $condition);
    }

    public function groupBy(string ...$columns): self
    {
        if (empty($columns)) return $this;
        if (! isset($this->groupByColumns)) $this->groupByColumns = [];

        array_push($this->groupByColumns, ...$columns);
        return $this;
    }

    public function having(string $aggregateCondition, bool $joinWithAnd=false): self
    {
        if (! isset($this->havingCondition)) $this->havingCondition = [];

        array_push($this->havingCondition, [$joinWithAnd, $aggregateCondition]);
        return $this;
    }

    public function orderBy(string|array ...$columns): self
    {
        // Check for valid order by columns argument
        $orderByColumns = array_map(function (string|array $column): array {
            if (is_string($column)) [$columnName, $order] = [$column, 'ASC'];
            else {
                if (count($column) !== 2)
                    throw new QueryBuilderException('Invalid ORDER BY column: order by column must contain exactly two items');

                [$columnName, $order] = $column;

                // Ensure the order provided is valid
                $acceptOrderBy = ['ASC', 'DESC'];
                if (! in_array($order = strtoupper($order), $acceptOrderBy))
                    throw new QueryBuilderException("Invalid ORDER BY column: $order");
            }

            return [
                'column'=> $columnName,
                'order' => $order
            ];
        }, $columns);

        $this->orderByColumns = $orderByColumns;
        return $this;
    }

    public function limit(int $noOfRows, int $offset=0): self
    {
        $this->selectLimit = [
            'rows'  => $noOfRows,
            'offset'=> $offset
        ];
        return $this;
    }

    // Build methods

    private function buildColumns(): string
    {
        if (! isset($this->columns))
            throw new QueryBuilderException('Columns not set');

        if (is_string($this->columns)) return $this->columns;

        return join(', ', array_map(function(array|string $column) {
            if (is_string($column)) return $column;

            [$colName, $alias] = $column;
            return "$colName AS '$alias'";
        }, $this->columns));
    }

    private function buildJoins(): string
    {
        return join(' ', array_map(function(array $joinInfo) {
            $table      = $joinInfo['table'];
            $joinType   = strtoupper($joinInfo['type']) . ' JOIN';
            $condition  = $this->buildCondition($joinInfo['conditions']);

            return "$joinType $table ON $condition";
        }, $this->joins));
    }

    private function buildGroupByColumns(): string
    {
        return join(', ', $this->groupByColumns);
    }

    private function buildHavingCondition(): string
    {
        return $this->buildCondition($this->havingCondition);
    }

    private function buildOrderByColumns(): string
    {
        return join(', ', array_map(
            fn($col): string => "{$col['column']} {$col['order']}",
            $this->orderByColumns
        ));
    }

    private function buildSelectLimit(): string
    {
        $limit = $this->selectLimit;
        return " {$limit['rows']} OFFSET {$limit['offset']}";
    }

    public function buildQuery(): string
    {
        $sql = "";

        if (! isset($this->table))
            throw new QueryBuilderException('Query table not set');

        $table  = $this->getTable();
        $columns= $this->buildColumns();

        // Basic SQL SELECT syntax
        $sql = "SELECT $columns FROM $table";

        $syntaxes = [
            'joins'             => '',
            'whereCondition'    => 'WHERE',
            'groupByColumns'    => 'GROUP BY',
            'havingCondition'   => 'HAVING',
            'orderByColumns'    => 'ORDER BY',
            'selectLimit'       => 'LIMIT'
        ];

        foreach($syntaxes as $property => $prefix) {
            if (isset($this->$property)) {
                $methodName = "build" . ucfirst($property);
                $sql .= " $prefix " . $this->$methodName();
            }
        }

        return "$sql;";
    }
}