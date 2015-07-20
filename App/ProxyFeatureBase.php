<?php

namespace Tale\App;

use Tale\App;

abstract class ProxyFeatureBase extends FeatureBase {

    public function __call( $method, array $args = null ) {

        $args = $args ? $args : [];

        return call_user_func_array( [ $this->getTarget(), $method ], $args );
    }

    public function __get( $key ) {

        return $this->getTarget()->{$key};
    }

    abstract public function getTarget();
}