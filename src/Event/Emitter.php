<?php

namespace Tale\Event;

use Tale\Event;

class Emitter {

    private $_events;

    public function __construct() {

        $this->_events = [];
    }

    public function getEvents() {

        return $this->_events;
    }

    public function getEvent( $name ) {

        if( !isset( $this->_events[ $name ] ) )
            $this->_events[ $name ] = new Event( $name );

        return $this->_events[ $name ];
    }

    public function bind( $name, callable $handler ) {

        $this->getEvent( $name )->addHandler( $handler );
        return $this;
    }

    public function unbind( $name, callable $handler ) {

        $this->getEvent( $name )->removeHandler( $handler );
        return $this;
    }

    public function emit( $name, Args $args = null ) {

        $args = $args ? $args : new Args();
        $event = $this->getEvent( $name );

        return $event( $args );
    }
}