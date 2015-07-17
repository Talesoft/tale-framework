<?php

namespace Tale\Cache\Adapter;

use Tale\Cache\AdapterBase;

class File extends AdapterBase {

    private $_path;

    protected function init() {

        $config = $this->getConfig();

        $this->_path = $config->path;
    }

    public function getPath() {

        return $this->_path;
    }

    public function getKeyPath( $key ) {

        $key = str_replace( '.', '/', $key );

        return $this->_path."/$key.tmp";
    }

    public function exists( $key, $lifeTime ) {

        $path = $this->getKeyPath( $key );
        return file_exists( $path ) && time() - filemtime( $path ) < $lifeTime;
    }

    public function get( $key ) {

        return unserialize( file_get_contents( $this->getKeyPath( $key ) ) );
    }

    public function set( $key, $value ) {

        $path = $this->getKeyPath( $key );
        $dir = dirname( $path );

        if( !is_dir( $dir ) )
            mkdir( $dir, 0777, true );

        file_put_contents( $path, serialize( $value ) );

        return $this;
    }

    public function remove( $key ) {

        unlink( $this->getKeyPath( $key ) );

        return $this;
    }
}