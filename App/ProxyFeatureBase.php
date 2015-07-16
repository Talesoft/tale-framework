<?php

namespace Tale\App;

use Tale\App,
    Tale\Config;

abstract class ProxyFeatureBase extends FeatureBase {

    abstract public function getProxiedObject();

    public function __call( $method, array $args = null ) {

        $args = $args ? $args : [];

        return call_user_func_array( [ $this->getProxiedObject(), $method ], $args );
    }
}