<?php

namespace Tale\Proxy;

trait CallTrait {

    abstract public function getCallProxyTarget();

    public function __call( $name, $arguments ) {

        return call_user_func_array( [ $this->getCallProxyTarget(), $name ], $arguments );
    }
}