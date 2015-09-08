<?php

namespace Tale\Crud\Type;

use Tale\Crud\Type;
use Tale\Crud\TypeBase;
use Tale\Crud\Validator;
use Traversable;

class ArrayType extends TypeBase implements \Countable, \IteratorAggregate, \ArrayAccess
{

    protected function sanitize($value)
    {

        if (!$this->isArray() && !$this->isScalar())
            return null;

        return $value;
    }

    protected function validate(Validator $v)
    {

        $v->whenSet(function(Validator $v) {

            $v->notArray('The value needs to be an array');
        });

        return parent::validate($v);
    }


    public function count()
    {

        $value = $this->getValue();
        return $value ? count($value) : 0;
    }

    public function getIterator()
    {

        $value = $this->getValue();
        if ($value)
            foreach ($value as $item)
                yield $item;

    }

    public function offsetExists($offset)
    {
        $value = $this->getValue();
        return $value ? isset($value[$offset]) : false;
    }

    public function offsetGet($offset)
    {

        $value = $this->getValue();
        return $value ? $value[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {

        throw new \Exception(
            "Failed to set value on ArrayType: "
            ."The value is read-only"
        );
    }

    public function offsetUnset($offset)
    {

        throw new \Exception(
            "Failed to unset value on ArrayType: "
            ."The value is read-only"
        );
    }
}