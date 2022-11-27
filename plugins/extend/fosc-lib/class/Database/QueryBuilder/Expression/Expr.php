<?php

namespace Fosc\Database\QueryBuilder\Expression;

class Expr
{
    public static function equal(string $column, string $value): string
    {
        return $column . ' = ' . $value;
    }

    public static function notEqual(string $column, string $value): string
    {
        return $column . ' <> ' . $value;
    }

    public static function greaterThan(string $column, string $value): string
    {
        return $column . ' > ' . $value;
    }

    public static function greaterThanEqual(string $column, string $value): string
    {
        return $column . ' >= ' . $value;
    }

    public static function lowerThan(string $column, string $value): string
    {
        return $column . ' < ' . $value;
    }

    public static function lowerThanEqual(string $column, string $value): string
    {
        return $column . ' <= ' . $value;
    }

    public static function isNull(string $column): string
    {
        return $column . ' IS NULL';
    }

    public static function isNotNull(string $column): string
    {
        return $column . ' IS NOT NULL';
    }

    public static function in(string $column, array $values): string
    {
        return $column . ' IN (' . implode(', ', $values) . ')';
    }

    public static function notIn(string $column, array $values): string
    {
        return $column . ' NOT IN (' . implode(', ', $values) . ')';
    }
}
