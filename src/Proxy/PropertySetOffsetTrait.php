<?php

namespace Tale\Proxy;

trait PropertySetOffsetTrait {
    use PropertyGetOffsetTrait;

    public function __unset( $name ) {

        $target = $this->getOffsetProxyTarget();
        unset( $target[ $name ] );
    }

    public function __set( $name, $value ) {

        $target = $this->getOffsetProxyTarget();
        $target[ $name ] = $value;
    }
}