<?php

namespace Tale\Crud;

use Tale\Data\Entity\State;
use Tale\Event;

abstract class EntityBase
{
    use Event\OptionalTrait;

    private $_data;
    private $_state;

    public function __construct(array $data = null, $initialState = null)
    {

        $this->_data = $data ? $data : [];
        $this->_state = $initialState ? $initialState : State::INITIALIZED;
    }

    abstract public function exists();

    public function create()
    {

        if (!$this->emit('beforeCreate', new EventArgs['entity' => $this]))
        $this->_state = State::CREATED;

        return $this;
    }

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

    public function remove()
    {

        $this->_state = State::REMOVED;

        return $this;
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

    protected function registerChange($name, $oldValue, $newValue)
    {

        $args = new Event\Args([
            'name' => $name,
            'oldValue' => $oldValue,
            'newValue' => $newValue
        ]);

        if ($this->emit('beforeChange', $args) && $this->emit("beforeChange:$name", $args)) {

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