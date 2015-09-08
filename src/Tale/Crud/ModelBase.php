<?php

namespace Tale\Crud;

abstract class ModelBase
{

    private static $_fields;

    protected function initEvents() {}
    protected function getExposedFields() {

        return [];
    }

    protected function validate(Validator $v)
    {

        return $v;
    }

    protected function getTableName() {}
    protected function getPrimaryColumn() {}

    public static function &getFieldCache()
    {

        if (!isset(self::$_fields))
            self::$_fields = [];

        $className = get_called_class();
        if (!isset(self::$_fields[$className]))
            self::$_fields[$className] = [];

        $ref = new \ReflectionClass($className);
        $defaults = $ref->getDefaultProperties();
        $props = $ref->getProperties(\ReflectionProperty::IS_STATIC);
        foreach ($props as $prop) {

            if ($prop->isPrivate())
                continue;


        }
    }
}