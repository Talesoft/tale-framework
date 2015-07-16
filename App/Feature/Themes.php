<?php

namespace Tale\App\Feature;

use Tale\App\FeatureBase;

class Themes extends FeatureBase {

    protected function init() {

        $app = $this->getApp();
        $config = $this->getConfig();

        var_dump( 'INIT THEMES FEATURE' );
    }
}