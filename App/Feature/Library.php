<?php

namespace Tale\App\Feature;

use Tale\App\FeatureBase,
    Tale\ClassLoader;

class Library extends FeatureBase {

    private $_classLoader;

    protected function init() {

        $app = $this->getApp();
        $config = $this->getConfig();

        $path = isset( $config->path ) ? $config->path : null;
        $nameSpace = isset( $config->nameSpace ) ? $config->nameSpace : null;
        $pattern = isset( $config->pattern ) ? $config->pattern : null;

        $this->_classLoader = new ClassLoader( $path, $nameSpace, $pattern );
        $this->_classLoader->register();
    }

    public function getClassLoader() {

        return $this->_classLoader;
    }
}