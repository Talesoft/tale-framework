<?php

namespace Tale\App\Feature;

use Tale\App\FeatureBase;
use Tale\ClassLoader;

class Library extends FeatureBase
{

    private $_loader;

    public function init()
    {

        $app = $this->getApp();

        $this->prependOptions([
            'path'              => $app->getOption('path').'/library',
            'nameSpace'         => null,
            'pattern'           => null
        ]);

        $app->bind('beforeRun', function () {

            $this->_loader = new ClassLoader(
                $this->getOption('path'),
                $this->getOption('nameSpace'),
                $this->getOption('pattern')
            );
            $this->_loader->register();
        });

        $app->bind('afterRun', function () {

            $this->_loader->unregister();
        });
    }

    public function isPrioritised()
    {
        return true;
    }
}