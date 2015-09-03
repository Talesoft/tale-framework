<?php

namespace Tale\App\Feature;

use Tale\App\FeatureBase;
use Tale\Data\Source;

class Data extends FeatureBase {

    private $_sources;

    protected function init() {

        if (!class_exists('Tale\\Data\\Source'))
            throw new \RuntimeException(
                "Failed to load data feature: "
                ."The data source class wasnt found. "
                ."Maybe you need the Tale\\Data namespace?"
            );

        $app = $this->getApp();

        $cache =
        $this->prependOptions([
            'path'              => $app->getOption('path').'/controllers',
            'nameSpace'         => null,
            'loadPattern'       => null,
            'classNamePattern'  => '%sController',
            'methodNamePattern' => '%Action',
            'args'              => [],
            'helpers'           => [],
            'createLoader'      => true,
            'errorController'   => 'error'
        ]);

        $this->bind('load', function () {

            $this->_initLoader();
            $this->_initFactory();
            $this->_initDispatcher();

            $this->_instances = [];

            $this->_args = $this->getOption('args');
            $this->_helpers = $this->getOption('helpers');

            $this->registerHelper('dispatch', [$this, 'dispatch']);

            var_dump('CONTROLLERS LOADED', $this);
        });

        $this->bind('unload', function () {

            if ($this->_loader)
                $this->_loader->unregister();

            unset($this->_loader);
            unset($this->_factory);
            unset($this->_dispatcher);

            unset($this->_instances);

            var_dump('CONTROLLERS UNLOADED');
        });

        var_dump( "DATA", $config );

        $this->_source = new Source( $config->getItems() );

        if( isset( $app->cache ) ) {

            $cache = $app->cache->createSubCache( 'data' );

            var_dump( $cache );
            $this->_source->setCache( $cache );
        }
    }

    public function getCallProxyTarget() {

        return $this->_source;
    }
}