<?php

namespace Tale\Data;

abstract class EntityBase {

    private $_synced;

    public function __construct() {

        $this->_synced = false;
    }

    public function isSynced() {

        return $this->_synced;
    }

    protected function sync() {

        $this->_synced = true;

        return $this;
    }

    protected function unsync() {

        $this->_synced = false;

        return $this;
    }

    public function loadIfExists() {

        if( $this->exists() )
            $this->load();

        return $this;
    }

    public function saveIfExists() {

        if( $this->exists() )
            $this->save();

        return $this;
    }

    public function createIfNotExists( array $data = null ) {

        if( !$this->exists() )
            $this->create( $data );

        return $this;
    }

    public function removeIfExists() {

        if( $this->exists() )
            $this->remove();

        return $this;
    }

    abstract public function exists();

    abstract public function load();
    abstract public function save();
    abstract public function create( array $data = null );
    abstract public function remove();
}