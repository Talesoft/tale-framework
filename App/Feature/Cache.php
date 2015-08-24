<?php

namespace Tale\App\Feature;

use Tale\App\ProxyFeatureBase,
    Tale\Cache as TaleCache;

class Cache extends ProxyFeatureBase {

    private $_cacheInstance;

    public function run() {

        $config = $this->getConfig();

        $this->_cacheInstance = new TaleCache( $config->getItems() );
    }

    public function getTarget() {

        return $this->_cacheInstance;
    }
}