<?php

namespace Tale\App;

use Tale\App,
    Tale\Config;

abstract class FeatureBase {

    private $_app;
    private $_config;

    public function __construct( App $app, array $options = null ) {

        $this->_app = $app;
        $this->_config = new Config( $options );

        $this->init();
    }

    public function getApp() {

        return $this->_app;
    }

    public function getConfig() {

        return $this->_config;
    }

    abstract protected function init();
}