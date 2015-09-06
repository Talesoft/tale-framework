<?php

namespace Tale\Data;

use Tale\Data\Entity\Collection;
use Tale\StringUtil;

class Table extends NamedEntityBase
{

    private $_database;
    private $_rowClassName;
    private $_columns;

    public function __construct(Database $database, $name, $rowClassName = null)
    {
        parent::__construct($name);

        $this->_database = $database;
        $this->_rowClassName = $rowClassName ? $rowClassName : 'Tale\\Data\\Row';
        $this->_columns = [];
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

        return parent::load();
    }

    public function save()
    {

        $this->getSource()->saveTable($this);

        return parent::save();
    }

    public function create()
    {

        if (empty($this->_columns))
            throw new \Exception(
                "Failed to create table: "
                ."No columns given"
            );

        $this->getSource()->createTable($this, $this->_columns);

        return parent::create();
    }

    public function remove()
    {

        $this->getSource()->removeTable($this);

        return parent::remove();
    }

    public function getColumns()
    {

        $source = $this->getSource();
        $database = $this->getDatabase();
        $columnNames = $source->fetchCached(
            "databases.$database.tables.$this.column-names",
            function () use($source) {

                return iterator_to_array($source->getColumnNames($this));
        }, $source->getOption('lifeTime'));

        foreach ($columnNames as $name)
            if (!isset($this->_columns[$name]))
                $this->_columns[$name] = new Column($this, $name);

        return new Entity\Collection($this->_columns);
    }

    public function setColumn($name, $typeString = null)
    {

        $this->_columns[$name] = new Column($this, $name, $typeString);

        return $this;
    }

    public function getColumn($name, $typeString = null)
    {

        if( !isset($this->_columns[$name]))
            $this->_columns[$name] = new Column($this, $name, $typeString);

        return $this->_columns[$name];
    }

    public function removeColumn($name)
    {

        unset($this->_columns[$name]);

        return $this;
    }

    public function getPrimaryColumn()
    {

        foreach ($this->getColumns()->loadAll() as $col)
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

        foreach ($this->getColumns()->loadAll() as $col)
            if ($col->getReference()) {

                $name = $col->getName();
                yield $name => $col;
            }
    }

    public function getReferencingColumns()
    {

        foreach ($this->getDatabase()->getTables() as $table)
            foreach ($table->getColumns()->loadAll() as $col) {

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