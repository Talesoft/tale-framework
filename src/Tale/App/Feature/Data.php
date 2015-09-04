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

            var_dump('DBCACHE', $cache ? get_class($cache) : $cache);

            foreach ($this->getConfig() as $name => $options) {

                $this->_sources[$name] = new Source($options);

                if ($cache)
                    $this->_sources[$name]->setCacheManager($cache->getManager());
            }

            var_dump('DATA SOURCES LOADED', $this);
        });

        $app->bind('afterRun', function () {

            foreach ($this->_sources as $source)
                if ($source->isOpen())
                    $source->close();

            var_dump('DATA SOURCES UNLOADED');
        });
    }

    public function getDependencies()
    {

        return [
            'cache' => 'Tale\\App\\Feature\\Cache'
        ];
    }
}