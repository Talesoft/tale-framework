<?php

namespace Tale\Data;

use Tale\StringUtil;

class Table extends NamedEntityBase
{

    const DEFAULT_ROW_CLASS_NAME = 'Tale\\Data\\Row';

    private $_database;
    private $_rowClassName;

    public function __construct(Database $database, $name, $load = false)
    {
        parent::__construct($name);

        $this->_database = $database;
        $this->_rowClassName = self::DEFAULT_ROW_CLASS_NAME;

        if ($load)
            $this->load();
    }

    public function getDatabase()
    {

        return $this->_database;
    }

    public function getSource()
    {

        return $this->_database->getSource();
    }

    public function getRowClassName()
    {

        $modelClassName = $this->getSource()->getModelClassName($this->getName());

        if ($modelClassName)
            return $modelClassName;

        return $this->_rowClassName;
    }

    public function setRowClassName($className)
    {

        $this->_rowClassName = $className;

        return $this;
    }

    public function exists()
    {

        return $this->getSource()->hasTable($this);
    }

    public function load()
    {

        $this->getSource()->loadTable($this);

        return $this->sync();
    }

    public function save()
    {

        $this->getSource()->saveTable($this);

        return $this->sync();
    }

    public function create(array $columns = null)
    {

        $this->getSource()->createTable($this, $columns);

        return $this->sync();
    }

    public function remove()
    {

        $this->getSource()->removeTable($this);

        return $this->unsync();
    }

    public function getColumns($load = false)
    {

        foreach ($this->getSource()->getColumnNames($this) as $name)
            yield $name => $this->getColumn($name, $load);
    }

    public function getColumnArray($load = false)
    {

        return iterator_to_array($this->getColumns($load));
    }

    public function getColumn($name, $load = false)
    {

        $className = $this->getSource()->getConfig()->columnClassName;

        if (is_string($load))
            return new $className($this, $name, false, $load);
        else
            return new $className($this, $name, $load);
    }

    public function getPrimaryColumn()
    {

        foreach ($this->getColumns(true) as $col)
            if ($col->isPrimary())
                return $col;

        return null;
    }

    public function getPrimaryKeyName($inflect = false)
    {

        $col = $this->getPrimaryColumn();
        $name = $col->getName();

        return $col
            ? ($inflect
                ? $this->inflectOutputColumnName($name)
                : $name
            )
            : null;
    }

    public function getReferenceColumns()
    {

        foreach ($this->getColumns(true) as $col)
            if ($col->getReference()) {

                $name = $col->getName();
                yield $name => $col;
            }
    }

    public function getReferencingColumns()
    {

        foreach ($this->getDatabase()->getTables(true) as $table)
            foreach ($table->getColumns(true) as $col) {

                $ref = $col->getReference();

                $name = $col->getName();
                if ($ref && ($ref->getTable()->getName() === $this->getName()))
                    yield $name => $col;
            }
    }

    public function equals(Table $otherTable)
    {

        return $this->getName() === $otherTable->getName();
    }

    public function getReferenceKeyNames(Table $otherTable, $columnName = null)
    {

        $inflectedColumnName = $this->inflectInputColumnName($columnName);

        $refCols = $this->getReferenceColumns();
        foreach ($refCols as $refColName => $refCol) {

            $ref = $refCol->getReference();
            if ($ref->belongsTo($otherTable) && (!$columnName || $inflectedColumnName === $refColName)) {

                $thisKey = $this->inflectOutputColumnName($refColName);
                $otherKey = $this->inflectOutputColumnName($ref->getName());

                return [$thisKey, $otherKey];
            }
        }

        throw new \Exception("Failed to get reference column names: No reference from $this to $otherTable found");
    }

    public function getReferencingKeyNames(Table $otherTable, $otherColumnName = null)
    {

        $otherInflectedColumnName = $this->inflectInputColumnName($otherColumnName);

        $refCols = $otherTable->getReferenceColumns();
        foreach ($refCols as $refColName => $refCol) {

            $ref = $refCol->getReference();
            if ($ref->belongsTo($this) && (!$otherColumnName || $otherInflectedColumnName === $refColName)) {

                $thisKey = $this->inflectOutputColumnName($ref->getName());
                $otherKey = $this->inflectOutputColumnName($refColName);

                return [$thisKey, $otherKey];
            }
        }

        throw new \Exception("Failed to get referencing column names: No reference from $otherTable to $this found");
    }

    public function createQuery(array $clauses = null, array $sortings = null, $limit = null, $limitStart = null)
    {

        return new Query($this, $clauses, $sortings, $limit, $limitStart);
    }

    public function insert(array $data)
    {

        $src = $this->getSource();
        $src->createRow($this, $data);

        return $src->getLastId();
    }

    protected function inflectInputColumnName($name)
    {

        return $this->getSource()->inflectInputColumnName($name);
    }

    protected function inflectOutputColumnName($name)
    {

        return $this->getSource()->inflectOutputColumnName($name);
    }

    public function __get($name)
    {

        return $this->getColumn($this->getSource()->inflectColumnName($name));
    }

    public function __call($method, array $args = null)
    {

        $qry = $this->createQuery();

        return call_user_func_array([$qry, $method], $args);
    }
}