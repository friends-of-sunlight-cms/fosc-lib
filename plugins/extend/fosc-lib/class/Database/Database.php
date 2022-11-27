<?php

namespace Fosc\Database;

use Sunlight\Database\Database as SunlightDb;
use Sunlight\Database\DatabaseException;
use Sunlight\Database\RawSqlValue;
use Sunlight\Extend;

class Database
{
    /** @var \mysqli */
    protected $connection = null;
    /** @var string */
    static $prefix;

    public function __construct(\mysqli $mysqli, string $prefix)
    {
        $this->connection = $mysqli;
        self::$prefix = $prefix;
    }

    public static function getSystemConnection(): self
    {
        return new self(SunlightDb::$mysqli, SunlightDb::$prefix);
    }

    /**
     * Run a SQL query
     *
     * @param string $query sql query with '?' as a placeholder value
     * @param array $bind array of values for '?' placeholders
     * @param bool $expectError don't throw an exception on failure 1/0
     * @param bool $event trigger an extend event 1/0
     * @return \mysqli_result|false
     * @throws \Exception|DatabaseException
     */
    public function select(string $query = '', array $bind = [], bool $expectError = false, bool $event = true)
    {
        // compose a loggable sql query - DON'T CALL DIRECTLY!
        $loggableSql = vsprintf(str_replace('?', '%s', $query), $bind);

        if ($event) {
            Extend::call('db.query', ['sql' => $loggableSql]);
        }

        try {
            $stmt = $this->executeStatement($query, $bind);
            $result = $stmt->get_result();//->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $result;
        } catch (\mysqli_sql_exception $e) {
            if ($expectError) {
                return false;
            }
            throw new DatabaseException(sprintf("%s\n\nSQL: %s", $e->getMessage(), $loggableSql), 0, $e);
        } finally {
            if ($event) {
                Extend::call('db.query.after', ['sql' => $loggableSql]);
            }
        }
    }

    /**
     * Run a SQL query and return the first result
     * @param string $sql sql query with '?' as a placeholder value
     * @param array $bind array of values for '?' placeholders
     * @param bool $expectError don't throw an exception on failure 1/0
     * @return array|false
     * @throws \Exception
     */
    public function selectRow(string $sql, array $bind = [], bool $expectError = false): ?array
    {
        $result = $this->select($sql, $bind, $expectError);

        if ($result === false) {
            return null;
        }

        return $this->row($result);
    }

    /**
     * Run a SQL query and return all rows
     *
     * @param string $sql sql query with '?' as a placeholder value
     * @param array $bind array of values for '?' placeholders
     * @param int|string|null $indexBy index the resulting array using the given column
     * @param int|string|null $fetchColumn only fetch the given column instead of the entire row
     * @param bool $assoc fetch rows as associative arrays 1/0
     * @param bool $expectError don't throw an exception on failure 1/0
     * @return array[]|false
     * @throws \Exception
     */
    public function selectRows(string $sql, array $bind = [], $indexBy = null, $fetchColumn = null, bool $assoc = true, bool $expectError = false): ?array
    {
        $result = $this->select($sql, $bind, $expectError);

        if ($result === false) {
            return null;
        }

        return $this->rows($result, $indexBy, $fetchColumn, $assoc);
    }

    /**
     * Count number of rows in a table using a condition
     *
     * @param string $table table name (no prefix)
     * @throws \Exception
     */
    public function count(string $table, string $where = '1', array $bind = []): int
    {
        $result = $this->select('SELECT COUNT(*) FROM ' . self::table($table) . ' WHERE ' . $where, $bind);

        if ($result instanceof \mysqli_result) {
            return (int)$this->result($result);
        }

        return 0;
    }

    /**
     * List table names by common prefix
     *
     * Uses system prefix if none is given.
     * @return string[]
     * @see \Sunlight\Database\Database::getTablesByPrefix()
     */
    public function getTablesByPrefix(?string $prefix = null): array
    {
        return SunlightDb::getTablesByPrefix($prefix);
    }

    /**
     * Run a SQL query
     *
     * @param bool $expectError don't throw an exception on failure 1/0
     * @param bool $event trigger an extend event 1/0
     * @throws DatabaseException
     * @return \mysqli_result|false
     * @see \Sunlight\Database\Database::query()
     */
    public function query(string $sql, bool $expectError = false, bool $event = true)
    {
        return SunlightDb::query($sql, $expectError, $expectError);
    }

    /**
     * Run a SQL query and return the first result
     *
     * @param bool $expectError don't throw an exception on failure 1/0
     * @return array|false
     * @see \Sunlight\Database\Database::queryRows()
     */
    public function queryRow(string $sql, bool $expectError = false)
    {
        return SunlightDb::queryRow($sql, $expectError);
    }

    /**
     * Run a SQL query and return all rows
     *
     * @param int|string|null $indexBy index the resulting array using the given column
     * @param int|string|null $fetchColumn only fetch the given column instead of the entire row
     * @param bool $assoc fetch rows as associative arrays 1/0
     * @param bool $expectError don't throw an exception on failure 1/0
     * @return array[]|false
     * @see \Sunlight\Database\Database::queryRows()
     */
    public function queryRows(string $sql, $indexBy = null, $fetchColumn = null, bool $assoc = true, bool $expectError = false)
    {
        return SunlightDb::queryRows($sql, $indexBy, $fetchColumn, $assoc, $expectError);
    }


    /**
     * Get a single row from a result
     * @return array|false
     * @see \Sunlight\Database\Database::row()
     */
    public function row(\mysqli_result $result)
    {
        return SunlightDb::row($result);
    }

    /**
     * Get all rows from a result
     *
     * @param int|string|null $indexBy index the resulting array using the given column
     * @param int|string|null $fetchColumn only fetch the given column instead of the entire row
     * @param bool $assoc fetch rows as assoiative arrays 1/0
     * @return array[]
     * @see \Sunlight\Database\Database::rows()
     */
    public function rows(\mysqli_result $result, $indexBy = null, $fetchColumn = null, bool $assoc = true): array
    {
        return SunlightDb::rows($result, $indexBy, $fetchColumn, $assoc);
    }

    /**
     * Get a single row from a result using numeric indexes
     *
     * @return array|false
     * @see \Sunlight\Database\Database::rown()
     */
    public function rown(\mysqli_result $result)
    {
        return SunlightDb::rown($result);
    }

    /**
     * Get a single column from the first result
     * @see \Sunlight\Database\Database::result()
     */
    public function result(\mysqli_result $result, int $column = 0)
    {
        return SunlightDb::result($result, $column);
    }

    /**
     * Get a list of columns in the given result
     * @see \Sunlight\Database\Database::columns()
     */
    public function columns(\mysqli_result $result): array
    {
        return SunlightDb::columns($result);
    }

    /**
     * Get number of rows in a result
     * @see \Sunlight\Database\Database::size()
     */
    public function size(\mysqli_result $result): int
    {
        return SunlightDb::size($result);
    }

    /**
     * Get AUTO_INCREMENT ID of last inserted row
     */
    public function insertID(): int
    {
        return $this->connection->insert_id;
    }

    /**
     * Get number of rows affected by the last query
     */
    public function affectedRows(): int
    {
        return $this->connection->affected_rows;
    }

    /**
     * Get prefixed table name
     * @see \Sunlight\Database\Database::table()
     */
    public static function table(string $name): string
    {
        return SunlightDb::table($name);
    }

    /**
     * Escape a string for use in a query
     *
     * This function does not add quotes - {@see \Sunlight\Database\Database::val()}.
     * @see \Sunlight\Database\Database::esc()
     */
    public function esc(string $value): string
    {
        return SunlightDb::esc($value);
    }

    /**
     * Escape a value to be used as an identifier (table or column name)
     * @see \Sunlight\Database\Database::escIdt()
     */
    public function escIdt(string $identifier): string
    {
        return SunlightDb::escIdt($identifier);
    }

    /**
     * Compose a list of identifiers separated by commas
     * @see \Sunlight\Database\Database::idtList()
     */
    public function idtList(array $identifiers): string
    {
        return SunlightDb::idtList($identifiers);
    }

    /**
     * Escape special wildcard characters in a string ("%" and "_")
     * @see \Sunlight\Database\Database::escWildcard()
     */
    public function escWildcard(string $string): string
    {
        return SunlightDb::escWildcard($string);
    }

    /**
     * Format a value to be used in a query, including quotes if necessary
     * @see \Sunlight\Database\Database::val()
     */
    public function val($value): string
    {
        return SunlightDb::val($value);
    }

    /**
     * Create a RAW sql value that will be ignored by {@see Database::val()}
     * @see \Sunlight\Database\Database::raw()
     */
    public function raw(string $safeSql): RawSqlValue
    {
        return SunlightDb::raw($safeSql);
    }

    /**
     * Create an equality condition
     *
     * @return string "=<value>" or "IS NULL"
     * @see \Sunlight\Database\Database::equal()
     */
    public function equal($value): string
    {
        return SunlightDb::equal($value);
    }

    /**
     * Create a non-equality condition
     *
     * @return string "!=<value>" or "IS NOT NULL"
     * @see \Sunlight\Database\Database::notEqual()
     */
    public function notEqual($value): string
    {
        return SunlightDb::notEqual($value);
    }

    /**
     * Format an array of values as a list of items separated by commas
     * @see \Sunlight\Database\Database::arr()
     */
    public function arr(array $arr): string
    {
        return SunlightDb::arr($arr);
    }

    /**
     * Insert a row
     *
     * @param string $table table name (no prefix)
     * @param array<string, mixed> $data associative array with row data
     * @param bool $getInsertId return AUTO_INCREMENT ID 1/0
     * @return bool|int
     * @see \Sunlight\Database\Database::insert()
     */
    public function insert(string $table, array $data, bool $getInsertId = false)
    {
        return SunlightDb::insert($table, $data, $getInsertId);
    }

    /**
     * Insert multiple rows
     *
     * If a column is missing in any of the rows, NULL will be used instead.
     *
     * @param string $table table name (no prefix)
     * @param array<array<string, mixed>> $rows list of associative arrays (rows) to insert
     * @see \Sunlight\Database\Database::insertMulti()
     */
    public function insertMulti(string $table, array $rows): bool
    {
        return SunlightDb::insertMulti($table, $rows);
    }

    /**
     * Update rows
     *
     * @param string $table table name (no prefix)
     * @param string $cond WHERE condition with '?' as a placeholder value
     * @param array $bind values
     * @param array<string, mixed> $changeset associative array with changes
     * @param int|null $limit max number of updated rows (null = no limit)
     * @throws \Exception
     */
    public function update(string $table, string $cond, array $bind, array $changeset, ?int $limit = 1): bool
    {
        if (empty($changeset)) {
            return false;
        }

        $counter = 0;
        $set_list = '';

        foreach ($changeset as $col => $val) {
            if ($counter !== 0) {
                $set_list .= ',';
            }

            $set_list .= $this->escIdt($col) . '=' . $this->val($val);
            ++$counter;
        }

        return $this->select('UPDATE ' . self::table($table) . " SET {$set_list} WHERE {$cond}" . (($limit === null) ? '' : " LIMIT {$limit}"), $bind);
    }

    /**
     * Update rows using a list of identifiers
     *
     * @param string $table table name (no prefix)
     * @param string $idColumn identifier column name
     * @param scalar[] $set list of identifiers
     * @param array<string, mixed> $changeset associative array with changes for all rows
     * @param int $maxPerQuery max number of identifiers per query
     * @see \Sunlight\Database\Database::updateSet()
     */
    public function updateSet(string $table, string $idColumn, array $set, array $changeset, int $maxPerQuery = 100): void
    {
        SunlightDb::updateSet($table, $idColumn, $set, $changeset, $maxPerQuery);
    }

    /**
     * Update rows using a map of changes
     *
     * @param string $table table name (no prefix)
     * @param string $idColumn identifier column name
     * @param array<scalar, array<string, mixed>> $changesetMap map of identifiers to changesets: array(id1 => changeset1, ...)
     * @param int $maxPerQuery max number of identifiers per query
     * @see \Sunlight\Database\Database::updateSetMulti()
     */
    public function updateSetMulti(string $table, string $idColumn, array $changesetMap, int $maxPerQuery = 100): void
    {
        SunlightDb::updateSetMulti($table, $idColumn, $changesetMap, $maxPerQuery);
    }

    /**
     * Convert a changeset map to a list of common update sets
     *
     * @param array<scalar, array<string, mixed>> $changesetMap array(id1 => changeset1, ...)
     * @return array<array{set: array, changeset: array}>
     * @see \Sunlight\Database\Database::changesetMapToList()
     */
    public function changesetMapToList(array $changesetMap): array
    {
        return SunlightDb::changesetMapToList($changesetMap);
    }

    /**
     * Delete rows
     *
     * @param string $table table name (no prefix)
     * @param string $cond WHERE condition
     */
    public function delete(string $table, string $cond, array $bind): bool
    {
        return $this->select('DELETE FROM ' . self::table($table) . ' WHERE ' . $cond, $bind);
    }

    /**
     * Delete rows using a list of identifiers
     *
     * @param string $table table name (no prefix)
     * @param string $idColumn identifier column name
     * @param scalar[] $set list of identifiers
     * @param int $maxPerQuery max number of identifiers per query
     * @see \Sunlight\Database\Database::deleteSet()
     */
    public function deleteSet(string $table, string $idColumn, array $set, int $maxPerQuery = 100): void
    {
        SunlightDb::deleteSet($table, $idColumn, $set, $maxPerQuery);
    }

    /**
     * Format date and time
     *
     * @param int|null $timestamp timestamp or null (= current time)
     * @return string YY-MM-DD HH:MM:SS (no quotes)
     * @see \Sunlight\Database\Database::datetime()
     */
    public static function datetime(?int $timestamp = null): string
    {
        return SunlightDb::datetime($timestamp);
    }

    /**
     * Format date
     *
     * @param int|null $timestamp timestamp or null (= current date)
     * @return string YY-MM-DD (no quotes)
     * @see \Sunlight\Database\Database::date()
     */
    public static function date(?int $timestamp = null): string
    {
        return SunlightDb::date($timestamp);
    }

    /**
     * @throws \Exception
     */
    private function executeStatement(string $query = '', array $params = []): \mysqli_stmt
    {
        try {
            /** @var \mysqli_stmt|false $stmt */
            $stmt = $this->connection->prepare($query);
            if ($stmt === false) {
                throw new \Exception("Unable to do prepared statement: " . $query);
            }

            if (count($params) > 0) {
                $types = str_repeat('s', count($params)); //types
                $stmt->bind_param($types, ...$params); // bind array at once
            }

            $stmt->execute();

            return $stmt;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}