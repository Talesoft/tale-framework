<?php

namespace Tale\App\Feature\Model;

use Tale\Data\Table;

class Gateway
{

    private $_provider;
    private $_table;
    private $_hasModel;

    public function __construct(Provider $provider, Table $table)
    {

        $this->_provider = $provider;
        $this->_table = $table;
        $this->_hasModel = $provider->hasModelClass($table->getName());

        $this->_table->setRowClassName($provider->getModelClassName($table->getName()));
    }

    public function getProvider()
    {

        return $this->_provider;
    }

    /**
     * @return \Tale\Data\Table
     */
    public function getTable()
    {
        return $this->_table;
    }

    public function hasModel()
    {

        return $this->_hasModel;
    }

    public function getModelFields()
    {

        if (!$this->_hasModel) {

            $columns = $this->_table->getColumns();
            return array_combine(array_map(
                [$this->_table->getSource(), 'inflectColumnName'],
                array_keys($columns)),
                array_fill(0, count($columns), null)
            );
        }

        return $this->_provider->getModelFields($this->_table->getName());
    }

    public function getModelColumns()
    {

        foreach ($this->getModelFields() as $name => $typeString) {

            $column = $this->_table->{$name};
            $column->parse($typeString);

            yield $column->getName() => $typeString;
        }
    }

    public function migrate()
    {

        /** @var \Tale\Data\Column[] $modelColumns */
        $modelColumns = iterator_to_array($this->getModelColumns());
        /** @var \Tale\Data\Column[] $tableColumns */
        $tableColumns = $this->_table->getColumnArray( true );

        /** @var \Tale\Data\Column[] $removedColumns */
        $removedColumns = array_diff_key($tableColumns, $modelColumns);

        var_dump($modelColumns, $removedColumns);

        foreach ($modelColumns as $name => $column) {

            if (!$column->exists())
                $column->create();
            else if (!$column->equals($tableColumns[$name], false))
                $column->save();
        }

        foreach ($removedColumns as $column)
            $column->remove();

        return $this;
    }

    public function __call($method, array $args = null)
    {

        return call_user_func_array([$this->_table, $method], $args);
    }
}