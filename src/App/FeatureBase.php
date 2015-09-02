<?php

namespace Tale\App;

use Tale\App,
    Tale\Config;

abstract class FeatureBase extends Config\Container {

    private $_app;

    public function __construct( App $app, $options = null ) {
        parent::__construct( $options );

        $this->_app = $app;

        $this->init();
    }

    public function getApp() {

        return $this->_app;
    }

    protected function init() {}
    public function run() {}
}