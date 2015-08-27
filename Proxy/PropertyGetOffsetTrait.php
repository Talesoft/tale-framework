<?php

namespace Tale\Proxy;

trait PropertyGetOffsetTrait {

    abstract public function &getOffsetProxyTarget();

    public function __isset( $name ) {

        $target = $this->getOffsetProxyTarget();
        return isset( $target[ $name ] );
    }

    public function __get( $name ) {

        $target = $this->getOffsetProxyTarget();
        return $target[ $name ];
    }
}