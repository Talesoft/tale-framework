<?php

namespace Tale\App\Feature;

use Tale\App\FeatureBase;
use Tale\Data\Source;

class Feature extends FeatureBase {

    protected function init() {

        $app = $this->getApp();

        // $this is usable in the callbacks

        $this->bind('load', function () {

            //Load logic here

            var_dump('FEATURE LOADED');
        });

        $this->bind('run', function () {

            //Run logic here

            var_dump('FEATURE RAN');
        });

        $this->bind('unload', function () {

            //Unload logic here

            var_dump('FEATURE UNLOADED');
        });
    }
}