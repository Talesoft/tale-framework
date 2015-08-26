<?php

namespace Tale\App\Feature;

use Tale\App\FeatureBase;
use Tale\Cache as TaleCache;
use Tale\Proxy;

class Cache extends FeatureBase {
    use Proxy\CallTrait;

    private $_cacheInstance;

    public function run() {

        $config = $this->getConfig();
        $this->_cacheInstance = new TaleCache( $config->getItems() );
    }

    public function getCallProxyTarget() {

        return $this->_cacheInstance;
    }
}