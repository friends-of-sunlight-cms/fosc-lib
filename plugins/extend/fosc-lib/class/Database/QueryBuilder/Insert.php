<?php

namespace Fosc\Database\QueryBuilder;

use Fosc\Database\QueryBuilder\Interfaces\QueryInterface;
use Sunlight\Database\Database as DB;

class Insert implements QueryInterface
{
    /** @var string */
    private $table;
    /** @var array */
    private $values = [];

    public function __construct(string $table)
    {
        $table = DB::table($table);
        $this->table = $table;
    }

    public function __toString(): string
    {
        return 'INSERT INTO ' . $this->table . ' (' . implode(', ', array_keys($this->values)) . ') VALUES (' . implode(', ', $this->values) . ')';
    }

    /**
     * @param array $values [column1 => value1, ...]
     */
    public function setValues(array $values): self
    {
        $this->values += $values;
        return $this;
    }

    public function setValue(string $column, string $value): self
    {
        $this->values[$column] = $value;
        return $this;
    }
}
