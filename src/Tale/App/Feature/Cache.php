<?php

namespace Tale\App\Feature;

use Tale\App\FeatureBase;
use Tale\Cache\Manager as CacheManager;

class Cache extends FeatureBase
{

    private $_manager;

    protected function init()
    {

        if (!class_exists('Tale\\Cache\\Manager'))
            throw new \RuntimeException(
                "Failed to load cache feature: "
                ."The cache manager wasnt found. "
                ."Maybe you need the Tale\\Cache namespace?"
            );

        $this->prependOptions([
            'options' => [
                'path' => $this->getApp()->getOption('path').'/cache'
            ]
        ]);

        $this->bind('load', function () {

            $config = $this->getConfig();
            $this->_manager = new CacheManager($config->getItems());

            var_dump('CACHE LOADED');
        });

        $this->bind('unload', function () {

            unset($this->_manager);

            var_dump('CACHE UNLOADED');
        });
    }

    public function getManager()
    {

        return $this->_manager;
    }
}