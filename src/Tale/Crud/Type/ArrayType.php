<?php

namespace Tale\Crud\Type;

use Tale\Crud\Type;
use Tale\Crud\TypeBase;
use Tale\Crud\Validator;
use Traversable;

class ArrayType extends TypeBase implements \Countable, \IteratorAggregate, \ArrayAccess
{

    private $_items;
    private $_type;

    public function __construct($value, array $options = null, $type = null)
    {

        $this->_options = $options ? $options : [];
        $this->_type = $type;
    }

    public function getOptions()
    {

        return $this->_options;
    }

    public function addOptions($option, $label = null)
    {

        if (!$label)
            $this->_options[] = $option;
        else
            $this->_options[$label] = $option;

        return $this;
    }

    public function count()
    {

        return count($this->_items);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {

        foreach ($this->_items as $item)

    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {

        $value = $this->_items[$offset];

        return $value instanceof TypeBase ? $value : Type::convert($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {

        $this->_items[$offset] = $value;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     */
    public function offsetUnset($offset)
    {

        unset($this->_items[$offset]);
    }


    protected function validate(Validator $v)
    {

        $v->whenSet(function(Validator $v) {

            $v->notIn($this->_options, 'The value needs to be one of '.implode( ', ', $this->_options));
        });

        return parent::validate($v);
    }
}