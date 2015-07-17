<?php

namespace Tale;

class Event {

    private $_name;
    private $_handlers;

    public function __construct( $name, array $handlers = null ) {

        $this->_name = $name;
        $this->_handlers = $handlers ? $handlers : [];
    }

    public function getName() {

        return $this->_name;
    }

    public function getHandlers() {

        return $this->_handlers;
    }

    public function addHandler( callable $handler ) {

        $this->_handlers[] = $handler;

        return $this;
    }

    public function removeHandler( callable $handler ) {

        $i = array_search( $handler, $this->_handlers, true );

        if( $i !== false )
            unset( $this->_handlers[ $i ] );

        return $this;
    }

    public function __invoke( Event\Args $args = null ) {

        $args = $args ? $args : new Event\Args();

        foreach( $this->_handlers as $handler )
            if( call_user_func_array( $handler, func_get_args() ) === false )
                break;

        return $args->isDefaultPrevented();
    }
}