<?php

namespace Tale\App\Feature;

use Tale\App\ProxyFeatureBase,
    Tale\Cache as TaleCache;

class Cache extends ProxyFeatureBase {

    private $_cacheInstance;

    protected function init() {

        $app = $this->getApp();
        $config = $this->getConfig();

        $this->_cacheInstance = new TaleCache( $config->getOptions() );
    }

    public function getTarget() {

        return $this->_cacheInstance;
    }
}