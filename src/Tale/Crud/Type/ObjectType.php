<?php

namespace Tale\Crud\Type;

use Tale\Crud\Type;
use Tale\Crud\TypeBase;
use Tale\Crud\Validator;
use Traversable;

class ArrayType extends TypeBase
{

    protected function sanitize($value)
    {

        if (!$this->isObject())
            return null;

        return $value;
    }

    protected function validate(Validator $v)
    {

        $v->whenSet(function(Validator $v) {

            $v->notObject('The value needs to be an object');
        });

        return parent::validate($v);
    }

    public function __isset($key)
    {

        $value = $this->getValue();
        return $value ? isset($value->{$key}) : false;
    }

    public function __get($key)
    {

        $value = $this->getValue();
        return $value ? $value->{$key} : null;
    }
}