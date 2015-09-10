<?php

namespace Tale\Event;

use Tale\Event;

/**
 * Optional trait to let an object
 * act like an event emitter
 *
 * @package Tale\Event
 */
trait EmitterTrait
{

    /**
     * A cache for events
     * Also avoids events with duplicate names
     *
     * @var \Tale\Event[]
     */
    private $_events;

    public function getEventClassName()
    {

        return 'Tale\\Event';
    }

    /**
     * Returns an event by name.
     * If the event doesnt exist yet, it is created
     *
     * @param $name The name of the event
     *
     * @return \Tale\Event The unique event instance
     */
    public function getEvent($name)
    {

        if (!isset($this->_events))
            $this->_events = [];

        if (!isset($this->_events[$name])) {

            $className = $this->getEventClassName();
            $this->_events[$name] = new $className($name);
        }

        return $this->_events[$name];
    }

    /**
     * Binds a new callback to an event
     *
     * When the event is emitted, all callbacks bound
     * to the event are called
     *
     * The handler should have the following style:
     * function( \Tale\Event\Args $args ) {
     *
     *      //Optional
     *      $args->preventDefault();
     * }
     *
     * All php call_user_func-style callbacks are allowed
     *
     * @param string $name The name of the event
     * @param callable $handler The callback to call
     *
     * @return $this
     */
    public function bind($name, $handler)
    {

        $this->getEvent($name)->addHandler($handler);

        return $this;
    }

    /**
     * Removes an event hanndler from an event
     * This makes sense if you want an event called a few times only
     *
     * @param $name The name of the event
     * @param $handler The callback to remove
     *
     * @return $this
     */
    public function unbind($name, $handler)
    {

        $this->getEvent($name)->removeHandler($handler);

        return $this;
    }

    /**
     * Emits an event.
     *
     * When an event is emitted, all bound callbacks are called.
     * $args is passed as the first argument to each callback
     *
     * @param string $name The name of the event
     * @param \Tale\Event\Args|null $args The arguments to pass to the event
     * @param bool $reverse
     *
     * @return bool The value of !$args->isDefaultPrevented()
     */
    public function emit($name, Args $args = null, $reverse = false)
    {

        $args = $args ? $args : new Args();
        $event = $this->getEvent($name);

        return $event($args, $reverse);
    }
}