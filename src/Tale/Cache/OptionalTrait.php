<?php

namespace Tale\Cache;

use Tale\Cache;

trait OptionalTrait {

    /* @var \Tale\Cache\Manager */
    private $_cacheManager;

    public function hasCacheManager() {

        return $this->_cacheManager instanceof Manager;
    }

    public function getCacheManager() {

        return $this->_cacheManager;
    }

    public function setCacheManager( Manager $manager ) {

        $this->_cacheManager = $manager;

        return $this;
    }

    protected function fetchCached( $key, callable $action, $lifeTime = null ) {

        if( !$this->_cacheManager )
            return call_user_func( $key );

        return $this->_cacheManager->fetch( $key, $action, $lifeTime );
    }
}