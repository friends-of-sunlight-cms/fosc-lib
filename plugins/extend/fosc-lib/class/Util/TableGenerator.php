<?php

namespace Fosc\Util;

use Danek\TableGenerator\Table;

class TableGenerator extends Table
{
    /**
     * @param string|null $id
     * @param array $tableClasses
     * @param array|null $headerColumns
     * @param array|array[][]|null $bodyRows
     * @param array|array[][]|null $footerRows
     * @return Table
     */
    public static function create(
        string $id = null,
        array  $tableClasses = [],
        array  $headerColumns = null,
        array  $bodyRows = null,
        array  $footerRows = null
    ): Table
    {
        $instance = parent::create($id, $tableClasses);
        if ($headerColumns !== null) {
            $instance->setHeaderColumns($headerColumns);
        }
        if ($bodyRows !== null) {
            $instance->setBodyRows($bodyRows);
        }
        if ($footerRows !== null) {
            $instance->setFooterRows($footerRows);
        }
        return $instance;
    }

}