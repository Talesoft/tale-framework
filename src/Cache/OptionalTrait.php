<?php

namespace Tale\Cache;

use Tale\Cache;

trait OptionalTrait {

    /* @var Cache */
    private $_cache;

    public function hasCache() {

        return $this->_cache instanceof Cache;
    }

    public function getCache() {

        return $this->_cache;
    }

    public function setCache( Cache $cache ) {

        $this->_cache = $cache;
        $cache->bind( $this );

        return $this;
    }

    protected function createSubCache( $nameSpace ) {

        return $this->_cache->createSubCache( $nameSpace );
    }

    protected function loadWithCache( $key, callable $action, $lifeTime = null ) {

        if( !$this->_cache )
            return call_user_func( $key );

        return $this->_cache->load( $key, $action, $lifeTime );
    }
}