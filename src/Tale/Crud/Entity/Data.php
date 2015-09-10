<?php

namespace Tale\Crud\Entity;

use Tale\Data\EntityBase;
use Tale\Event;

class Data extends Collection
{
    use Event\EmitterTrait;

    private $_changes;

    public function __construct(array $items = null, EntityBase $entity)
    {
        parent::__construct($items, Collection::FLAG_MUTABLE | Collection::FLAG_PROPERTY_ACCESS);

        $this->_changes = [];
    }

    public function setItem($key, $value)
    {

        $oldValue = $this->hasItem($key) ? $this->getItem($key) : null;
        $change = new Change($key, $oldValue, $value);
        $this->_changes[] = new Change($key, $oldValue, $value);
    }

}