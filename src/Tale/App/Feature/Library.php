<?php

namespace Tale\App\Feature;

use Tale\App\FeatureBase,
    Tale\ClassLoader;

class Library extends FeatureBase {

    private $_classLoader;

    protected function init() {

        //Libraries are initialized before the features are running since we might need them in other initialization logic
        //or for custom features

        $config = $this->getConfig();

        $path = isset( $config->path ) ? $config->path : null;
        $nameSpace = isset( $config->nameSpace ) ? $config->nameSpace : null;
        $pattern = isset( $config->pattern ) ? $config->pattern : null;

        $this->_classLoader = new ClassLoader( $path, $nameSpace, $pattern );
        $this->_classLoader->register();
    }
}