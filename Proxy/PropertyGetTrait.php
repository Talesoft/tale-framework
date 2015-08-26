<?php

namespace Tale\Proxy;

trait PropertyGetTrait {

    abstract public function getPropertyProxyTarget();

    public function __isset( $name ) {

        $target = $this->getPropertyProxyTarget();
        return isset( $target->{$name} );
    }

    public function __get( $name ) {

        return $this->getPropertyProxyTarget()->{$name};
    }
}