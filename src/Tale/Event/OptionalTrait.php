<?php

namespace Tale\Event;

use Tale\Event;

trait OptionalTrait
{

    /**
     * @var \Tale\Event[]
     */
    private $_events;

    /**
     * @param $name
     *
     * @return \Tale\Event
     */
    public function getEvent($name)
    {

        if( !isset( $this->_events ) )
            $this->_events = [];

        if (!isset($this->_events[$name]))
            $this->_events[$name] = new Event($name);

        return $this->_events[$name];
    }

    public function bind($name, callable $handler)
    {

        $this->getEvent($name)->addHandler($handler);

        return $this;
    }

    public function unbind($name, callable $handler)
    {

        $this->getEvent($name)->removeHandler($handler);

        return $this;
    }

    public function emit($name, Args $args = null)
    {

        $args = $args ? $args : new Args();
        $event = $this->getEvent($name);

        return $event($args);
    }
}