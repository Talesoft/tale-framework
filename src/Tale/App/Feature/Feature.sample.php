<?php

namespace Tale\App\Feature;

use Tale\App\FeatureBase;
use Tale\Data\Source;

class Feature extends FeatureBase
{

    public function init()
    {

        $app = $this->getApp();

        // $this is usable in the callbacks

        $app->bind('beforeRun', function () {

            //Load logic here

            var_dump('FEATURE LOADED');
        });

        $app->bind('run', function () {

            //Run logic here

            var_dump('FEATURE RAN');
        });

        $app->bind('afterRun', function () {

            //Unload logic here

            var_dump('FEATURE UNLOADED');
        });
    }
}