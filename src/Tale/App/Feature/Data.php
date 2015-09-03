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

    protected function init()
    {

        if (!class_exists('Tale\\Data\\Source'))
            throw new \RuntimeException(
                "Failed to load data feature: "
                ."The data source class wasnt found. "
                ."Maybe you need the Tale\\Data namespace?"
            );

        $app = $this->getApp();


        $this->bind('load', function () use ($app) {

            $this->_sources = [];

            /**
             * @var \Tale\App\Feature\Cache|null $cache
             */
            $cache = $app->getFirstFeatureOfType('Tale\\App\\Feature\\Cache');

            foreach ($this->getConfig() as $name => $options) {

                $this->_sources[$name] = new Source($options);

                if ($cache)
                    $this->_sources[$name]->setCacheManager($cache->getManager());
            }

            var_dump('DATA SOURCES LOADED', $this);
        });

        $this->bind('unload', function () {

            foreach ($this->_sources as $source)
                if ($source->isOpen())
                    $source->close();

            var_dump('DATA SOURCES UNLOADED');
        });
    }
}