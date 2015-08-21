<?php

namespace Tale\App\Feature;

use Tale\App\FeatureBase;

class Controllers extends Library {

    private $_dispatcher;

    protected function init() {
        parent::init();

        $app = $this->getApp();
        $config = $this->getConfig();

        var_dump( 'INIT CONTROLLERS!' );
    }

    public function dispatch( $controller = null, $action = null, array $args = null ) {


    }
}