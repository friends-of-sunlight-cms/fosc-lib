<?php

namespace Fosc\Database\QueryBuilder;

use Fosc\Database\QueryBuilder\Interfaces\QueryInterface;
use Sunlight\Database\Database as DB;

class Update implements QueryInterface
{
    /** @var string */
    private $table;
    /** @var array */
    private $conditions = [];
    /** @var array */
    private $columns = [];

    public function __construct(string $table, ?string $alias = null)
    {
        $table = DB::table($table);
        $this->table = ($alias === null ? $table : $table . ' AS ' . $alias);
    }

    public function __toString(): string
    {
        return 'UPDATE ' . $this->table . ' SET ' . implode(', ', $this->columns) . (count($this->conditions) > 0 ? ' WHERE ' . implode(' AND ', $this->conditions) : '');
    }

    public function where(string ...$where): self
    {
        foreach ($where as $arg) {
            $this->conditions[] = $arg;
        }
        return $this;
    }

    /**
     * @param array $data [column1 => value1, ...]
     */
    public function sets(array $data): self
    {
        $this->columns += $data;
        return $this;
    }

    public function set(string $key, string $value): self
    {
        $this->columns[] = $key . ' = ' . $value;
        return $this;
    }
}
