<?php

namespace Fosc\Database\QueryBuilder;

use Fosc\Database\QueryBuilder\Interfaces\QueryInterface;
use Sunlight\Database\Database as DB;

class Delete implements QueryInterface
{
    /** @var string */
    private $table;
    /** @var array */
    private $conditions = [];

    public function __construct(string $table, ?string $alias = null)
    {
        $table = DB::table($table);
        $this->table = ($alias === null ? $table : $table . ' AS ' . $alias);
    }

    public function __toString(): string
    {
        return 'DELETE FROM ' . $this->table . (count($this->conditions) > 0 ? ' WHERE ' . implode(' AND ', $this->conditions) : '');
    }

    public function where(string ...$where): self
    {
        foreach ($where as $arg) {
            $this->conditions[] = $arg;
        }
        return $this;
    }
}
