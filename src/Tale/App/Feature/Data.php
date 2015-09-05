<?php

namespace Tale\App\Feature;

use Tale\App\FeatureBase;
use Tale\Data\Source;

class Data extends FeatureBase
{

    /**
     * @var \Tale\Data\Source[]
     */
    private $_sources;

    public function init()
    {

        if (!class_exists('Tale\\Data\\Source'))
            throw new \RuntimeException(
                "Failed to load data feature: "
                ."The data source class wasnt found. "
                ."Maybe you need the Tale\\Data namespace?"
            );

        $app = $this->getApp();


        $app->bind('beforeRun', function () use ($app) {

            $this->_sources = [];

            /**
             * @var \Tale\App\Feature\Cache|null $cache
             */
            $cache = $this->cache;

            /**
             * @var \Tale\App\Feature\Controller|null $controller
             */
            $controller = $this->controller;

            foreach ($this->getConfig() as $name => $options) {

                $this->_sources[$name] = new Source($options);

                if ($cache)
                    $this->_sources[$name]->setCacheManager($cache->getManager()->createSubManager('data'));
            }

            if ($controller)
                $controller->registerHelper('getDataSource', function($controller, $name) {

                    return $this->_sources[$name];
                });

            var_dump('DATA SOURCES LOADED');
        });

        $app->bind('afterRun', function () {

            foreach ($this->_sources as $source)
                if ($source->isOpen())
                    $source->close();

            var_dump('DATA SOURCES UNLOADED');
        });
    }

    public function getSources()
    {

        return $this->_sources;
    }

    public function getSource($name)
    {

        return $this->_sources[$name];
    }

    public function getDependencies()
    {

        return [
            'cache' => 'Tale\\App\\Feature\\Cache',
            'controller' => 'Tale\\App\\Feature\\Controller'
        ];
    }
}