<?php

namespace Fosc\Database\QueryBuilder;

use Fosc\Database\Database;
use Fosc\Database\QueryBuilder\Interfaces\QueryInterface;

class Select implements QueryInterface
{
    public const ORDER_ASC = 'ASC';
    public const ORDER_DESC = 'DESC';

    /** @var array */
    private $fields = [];
    /** @var array */
    private $conditions = [];
    /** @var array */
    private $order = [];
    /** @var array */
    private $from = [];
    /** @var array */
    private $groupBy = [];
    /** @var array */
    private $having = [];
    /** @var int|null */
    private $limit;
    /** @var bool */
    private $distinct = false;
    /** @var array */
    private $join = [];

    public function __construct(array $select)
    {
        $this->fields = $select;
    }

    public function select(string ...$select): self
    {
        foreach ($select as $arg) {
            $this->fields[] = $arg;
        }
        return $this;
    }

    public function __toString(): string
    {
        return trim('SELECT ' . ($this->distinct ? 'DISTINCT ' : '') . implode(', ', $this->fields)
            . ' FROM ' . implode(', ', $this->from)
            . (count($this->join) > 0 ? ' ' . implode(' ', $this->join) : '')
            . (count($this->conditions) > 0 ? ' WHERE ' . implode(' AND ', $this->conditions) : '')
            . (count($this->groupBy) > 0 ? ' GROUP BY ' . implode(', ', $this->groupBy) : '')
            . (count($this->having) > 0 ? ' HAVING ' . implode(' AND ', $this->having) : '')
            . (count($this->order) > 0 ? ' ORDER BY ' . implode(', ', $this->order) : '')
            . ($this->limit === null ? '' : ' LIMIT ' . $this->limit));
    }

    public function where(string ...$where): self
    {
        foreach ($where as $arg) {
            $this->conditions[] = $arg;
        }
        return $this;
    }

    public function from(string $table, ?string $alias = null): self
    {
        $table = Database::table($table);
        $this->from[] = ($alias === null ? $table : $table . ' AS ' . $alias);
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function orderBy(string $columnName, string $order = self::ORDER_ASC): self
    {
        $this->order[] = $columnName . ' ' . $order;
        return $this;
    }

    public function innerJoin(string $table, string $alias, string $onCond): self
    {
        $this->addJoin('INNER', $table, $alias, $onCond);
        return $this;
    }

    public function leftJoin(string $table, string $alias, string $onCond): self
    {
        $this->addJoin('LEFT', $table, $alias, $onCond);
        return $this;
    }

    public function rightJoin(string $table, string $alias, string $onCond): self
    {
        $this->addJoin('RIGHT', $table, $alias, $onCond);
        return $this;
    }

    private function addJoin(string $type, string $table, string $alias, string $onCond): void
    {
        $table = Database::table($table);
        $this->join[] = $type . ' JOIN ' . $table . ' ' . $alias . ' ON (' . $onCond . ')';
    }

    public function distinct(): self
    {
        $this->distinct = true;
        return $this;
    }

    public function groupBy(string ...$groupBy): self
    {
        foreach ($groupBy as $arg) {
            $this->groupBy[] = $arg;
        }
        return $this;
    }

    public function having(string ...$having): self
    {
        foreach ($having as $arg) {
            $this->having[] = $arg;
        }
        return $this;
    }
}
