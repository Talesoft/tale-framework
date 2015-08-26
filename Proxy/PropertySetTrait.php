<?php

namespace Tale\Proxy;

trait PropertySetTrait {
    use PropertyGetTrait;

    public function __unset( $name ) {

        $target = $this->getPropertyProxyTarget();
        unset( $target->{$name} );
    }

    public function __set( $name, $value ) {

        $target = $this->getPropertyProxyTarget();
        $target->{$name} = $value;
    }
}