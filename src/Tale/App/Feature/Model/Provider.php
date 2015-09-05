<?php

namespace Tale\App\Feature\Model;

use Tale\Data\Database;
use Tale\Util\StringUtil;

class Provider
{

    private $_nameSpace;
    private $_database;
    private $_gateways;

    public function __construct(Database $database, $nameSpace = null)
    {

        $this->_database = $database;
        $this->_nameSpace = $nameSpace;
        $this->_gateways = [];
    }

    /**
     * @return null
     */
    public function getNameSpace()
    {

        return $this->_nameSpace;
    }

    /**
     * @return \Tale\Data\Database
     */
    public function getDatabase()
    {

        return $this->_database;
    }

    public function getGateway($tableName)
    {

        //Sanitize tablename
        $tableName = StringUtil::variablize($tableName);

        if (!isset($this->_gateways[$tableName])) {

            $table = $this->_database->{$tableName};
            $modelClassName = $this->hasModelClass($tableName) ? $this->getModelClassName($tableName) : 'Tale\\Data\\Row';
            $gateway = new Gateway($table, $this->getModelClassName($modelClassName));

            $this->_gateways[$tableName] = $gateway;
        }

        return $this->_gateways[$tableName];
    }

    public function getModelClassName($tableName)
    {

        return ( $this->_nameSpace ? rtrim($this->_nameSpace, '\\').'\\' : '' ).StringUtil::camelize(StringUtil::singularize($tableName));
    }

    public function hasModelClass($tableName)
    {

        return class_exists($this->getModelClassName($tableName));
    }

    public function getModelFields($tableName)
    {

        $ref = new \ReflectionClass($this->getModelClassName($tableName));
        $defaultValues = $ref->getDefaultProperties();

        foreach ($ref->getProperties( \ReflectionProperty::IS_PUBLIC) as $prop) {

            if ($prop->isStatic())
                continue;

            $name = $prop->getName();
            yield $name => isset($defaultValues[$name]) ? $defaultValues[$name] : null;
        }
    }

    public function __call($method, array $args = null)
    {

        return call_user_func_array([$this->_database, $method], $args);
    }
}