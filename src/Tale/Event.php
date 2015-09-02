<?php

namespace Tale;

/**
 * Event class
 *
 * @package Tale
 */
class Event
{

    /**
     * @var string The name of the event
     */
    private $_name;
    /**
     * @var array The handlers attached to this event
     */
    private $_handlers;

    /**
     * Creates a new event
     *
     * The event can be invoked with $event( $args ) or
     * $event->trigger( $args )
     *
     * @param string     $name     The name of the event
     * @param array|null $handlers The handlers attached to this event
     */
    public function __construct($name, array $handlers = null)
    {

        $this->_name = $name;
        $this->_handlers = $handlers ? $handlers : [];
    }

    /**
     * @return string Returns the name of the event
     */
    public function getName()
    {

        return $this->_name;
    }

    /**
     * @return array Returns the currently attached handlers of this event
     */
    public function getHandlers()
    {

        return $this->_handlers;
    }

    /**
     * Adds a new handler to an event
     *
     * @param callable $handler The handler (Anonymous function, string, array)
     *
     * @return $this
     */
    public function addHandler(callable $handler)
    {

        $this->_handlers[] = $handler;

        return $this;
    }

    /**
     * Removes an assigned handler
     *
     * This uses array_search to find the handler
     *
     * @param callable $handler The handler to remove
     *
     * @return $this
     */
    public function removeHandler(callable $handler)
    {

        $i = array_search($handler, $this->_handlers, true);

        if ($i !== false)
            unset($this->_handlers[$i]);

        return $this;
    }

    /**
     * Triggers the event
     *
     * The return value is the isDefaultPrevented() result of the event args
     * If null is passed, new event args are created automatically
     *
     * @param \Tale\Event\Args|null $args An argument object to pass
     *
     * @return bool
     */
    public function trigger(Event\Args $args = null)
    {

        $args = $args ? $args : new Event\Args();

        foreach ($this->_handlers as $handler)
            if (call_user_func_array($handler, func_get_args()) === false)
                break;

        return $args->isDefaultPrevented();
    }

    /**
     * An alias for $this->trigger()
     *
     * @see Event->trigger()
     *
     * @param \Tale\Event\Args|null $args
     *
     * @return bool
     */
    public function __invoke(Event\Args $args = null)
    {

        return $this->trigger($args);
    }
}