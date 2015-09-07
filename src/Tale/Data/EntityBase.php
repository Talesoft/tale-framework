<?php

namespace Tale\Data;

use Tale\Data\Entity\State;
use Tale\Event;

abstract class EntityBase
{
    use Event\OptionalTrait;

    private $_state;

    public function __construct()
    {

        $this->_state = State::INITIALIZED;
    }

    public function loadIfExists()
    {

        if ($this->exists())
            $this->load();

        return $this;
    }

    public function saveIfExists()
    {

        if ($this->exists())
            $this->save();

        return $this;
    }

    public function createIfNotExists()
    {

        if (!$this->exists())
            $this->create();

        return $this;
    }

    public function removeIfExists()
    {

        if ($this->exists())
            $this->remove();

        return $this;
    }

    abstract public function exists();

    public function load()
    {

        $this->_state = State::LOADED;

        return $this;
    }

    public function save()
    {

        $this->_state = State::SAVED;

        return $this;
    }

    public function create()
    {

        $this->_state = State::CREATED;

        return $this;
    }

    public function remove()
    {

        $this->_state = State::REMOVED;

        return $this;
    }

    public function registerChange()
    {

        $this->_state = State::CHANGED;

        return $this;
    }

    public function isSynced($allowRecreation = false)
    {

        if ($this->_state === State::CHANGED)
            return false;

        $syncedStates = [
            State::CREATED,
            State::SAVED,
            State::LOADED
        ];

        if ($allowRecreation)
            $syncedStates[] = State::REMOVED;

        return in_array($this->_state, $syncedStates);
    }
}