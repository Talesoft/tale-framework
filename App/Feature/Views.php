<?php

namespace Tale\App\Feature;

use Tale\App\FeatureBase;

class Views extends FeatureBase {

    protected function init() {
        parent::init();

        $app = $this->getApp();
        $config = $this->getConfig();

        var_dump( 'INIT VIEWS FEATURE' );
    }
}