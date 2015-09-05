<?php

namespace Tale\App\Feature\Model;

use Tale\Data\Table;

class Gateway
{

    private $_provider;
    private $_table;

    public function __construct(Provider $provider, Table $table)
    {

        $this->_provider = $provider;
        $this->_table = $table;

        $this->_table->setRowClassName($provider->getModelClassName($table->getName()));
    }

    public function getProvider()
    {

        return $this->_provider;
    }

    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->_table;
    }

    public function getModelFields()
    {

        return $this->_provider->getModelFields($this->_table->getName());
    }

    public function getModelColumns()
    {

        foreach ($this->getModelFields() as $name => $typeString) {

            $column = $this->_table->{$name};
            $column->parse($typeString);

            yield $name => $typeString;
        }
    }

    public function migrate()
    {

    }

    public function __call($method, array $args = null)
    {

        return call_user_func_array([$this->_table, $method], $args);
    }
}