<?php

namespace Tale\App\Feature;

use Tale\App\FeatureBase;
use Tale\Cache\Manager as CacheManager;

class Cache extends FeatureBase
{

    /**
     * @var \Tale\Cache\Manager
     */
    private $_manager;

    public function init()
    {
        $app = $this->getApp();

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

        $app->bind('beforeRun', function () {

            $config = $this->getConfig();
            $this->_manager = new CacheManager($config->getItems());
        });

        $app->bind('afterRun', function () {

            unset($this->_manager);
        });
    }

    public function getManager()
    {

        return $this->_manager;
    }
}