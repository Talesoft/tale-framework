<?php

namespace Tale\Db;

use Tale\Cache;

abstract class ControllerBase {

    /**
     * @var AdapterBase
     */
    private $_adapter;

    /**
     * @var Cache
     */
    private $_cache;

    public function __construct( AdapterBase $adapter ) {

        $this->_adapter = $adapter;
        $this->_cache = null;
    }

    public function getAdapter() {

        return $this->_adapter;
    }

    public function getCache() {

        return $this->_cache;
    }

    public function setCache( Cache $cache ) {

        $this->_cache = $cache;
        $cache->bind( $this );

        return $this;
    }

    public function createIfNotExists() {

        if( !$this->exists() )
            $this->create();

        return $this;
    }

    public function removeIfExists() {

        if( $this->exists() )
            $this->remove();

        return $this;
    }

    abstract public function create();
    abstract public function exists();
    abstract public function load();
    abstract public function save();
    abstract public function remove();
}