<?php

namespace Tale\Crud\Entity;

use Tale\Data\EntityBase;

class Data extends Collection
{

    private $_changes;

    public function __construct(array $items = null, EntityBase $entity)
    {
        parent::__construct($items, Collection::FLAG_MUTABLE | Collection::FLAG_PROPERTY_ACCESS);

        $this->_changes = [];
    }

    public function setItem($key, $value)
    {

        $oldValue = $this->hasItem($key) ? $this->getItem($key) : null;
        $this->_changes[] = new Change($this->)
    }

}