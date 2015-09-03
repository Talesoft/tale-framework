<?php

namespace Tale\Data;

use Tale\Config;

abstract class AdapterBase
{
    use Config\OptionalTrait;


    public function __construct(array $options = null)
    {

        $this->appendOptions([
            'inflections' => [
                'databases'     => null,
                'tables'        => null,
                'columns'       => null,
                'inputColumns'  => null,
                'outputColumns' => null
            ]
        ]);

        if ($options)
            $this->appendOptions($options, true);
    }

    public function __destruct()
    {

        if ($this->isOpen())
            $this->close();
    }

    public function inflectDatabaseName($string)
    {

        $inf = $this->resolveOption('inflections.databases');
        if (!$inf)
            return $string;

        return call_user_func($inf, $string);
    }

    public function inflectTableName($string)
    {

        $inf = $this->resolveOption('inflections.tables');
        if (!$inf)
            return $string;

        return call_user_func($inf, $string);
    }

    public function inflectColumnName($string)
    {

        $inf = $this->resolveOption('inflections.columns');
        if (!$inf)
            return $string;

        return call_user_func($inf, $string);
    }

    public function inflectInputColumnName($string)
    {

        $inf = $this->resolveOption('inflections.inputColumns');
        if (!$inf)
            return $string;

        return call_user_func($inf, $string);
    }

    public function inflectOutputColumnName($string)
    {

        $inf = $this->resolveOption('inflections.outputColumns');
        if (!$inf)
            return $string;

        return call_user_func($inf, $string);
    }


    abstract public function open();

    abstract public function close();

    abstract public function isOpen();

    abstract public function encode($value);

    abstract public function decode($value);


    abstract public function getDatabaseNames();

    abstract public function hasDatabase(Database $database);

    abstract public function loadDatabase(Database $database);

    abstract public function saveDatabase(Database $database);

    abstract public function createDatabase(Database $database);

    abstract public function removeDatabase(Database $database);


    abstract public function getTableNames(Database $database);

    abstract public function hasTable(Table $table);

    abstract public function loadTable(Table $table);

    abstract public function saveTable(Table $table);

    abstract public function createTable(Table $table, array $columns);

    abstract public function removeTable(Table $table);


    abstract public function getColumnNames(Table $table);

    abstract public function hasColumn(Column $column);

    abstract public function loadColumn(Column $column);

    abstract public function saveColumn(Column $column);

    abstract public function createColumn(Column $column);

    abstract public function removeColumn(Column $column);


    abstract public function countRows(Query $query, $field = null, $distinct = false);

    abstract public function loadRows(Query $query, array $fields = null, $as = null);

    abstract public function saveRows(Query $query, array $data);

    abstract public function createRow(Table $table, array $data);

    abstract public function removeRows(Query $query);

    abstract public function getLastId();
}