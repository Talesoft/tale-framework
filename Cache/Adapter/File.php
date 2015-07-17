<?php

namespace Tale\Cache\Adapter;

use Tale\Cache\AdapterBase;

/**
 * Basic file system storage cache adapter
 *
 * Given a path it uses a directory and files as a cache storage
 * Sub-Cache keys are mapped to directories (e.g. some.sub.key => some/sub/key.cache.php)
 *
 * @package Tale\Cache\Adapter
 */
class File extends AdapterBase {

    /**
     * The directory that will be used as a cache storage
     *
     * @var string
     */
    private $_path;

    /**
     * The path to the file that contains the life-times for each key in this cache
     *
     * @var string
     */
    private $_lifeTimePath;

    /**
     * The life-times of specific items indexed by the cache-key used
     *
     * @var array
     */
    private $_lifeTimes;

    /**
     * Initializes the file cache adapter
     */
    protected function init() {

        $config = $this->getConfig();

        $this->_path = $config->path;
        $this->_lifeTimePath = $this->_path.'/.life-times.json';
        $this->_lifeTimes = [];

        if( file_exists( $this->_lifeTimePath ) )
            $this->_lifeTimes = json_decode( file_get_contents( $this->_lifeTimePath ), true );
    }


    /**
     * Returns the current cache storage directory path
     *
     * @return string
     */
    public function getPath() {

        return $this->_path;
    }

    /**
     * Translates a cache key to the specific cache storage path
     * Dots (.) around the key will be trimmed
     *
     * @param $key string The key that needs to be translated
     *
     * @return string The path where the cache file resides
     */
    public function getKeyPath( $key ) {

        $key = str_replace( '.', '/', trim( $key, '.' ) );

        return $this->_path."/$key.cache.json";
    }

    /**
     * Checks if the given cache key has an existing cache file that didn't exceed the given life-time
     *
     * @param $key string The key that needs to be checked
     *
     * @return bool
     */
    public function exists( $key ) {

        $path = $this->getKeyPath( $key );

        if( !file_exists( $path ) || empty( $this->_lifeTimes[ $key ] ) )
            return false;

        if( time() - filemtime( $path ) > $this->_lifeTimes[ $key ] )
            return false;

        return true;
    }

    /**
     * Gets the content of a cache file by its key
     *
     * @param $key string The key that needs to be checked
     *
     * @return mixed The cached content value
     */
    public function get( $key ) {

        return json_decode( file_get_contents( $this->getKeyPath( $key ) ), true );
    }

    /**
     * Sets the value of an cache item to the given value
     *
     * @param $key string The key that needs to be checked
     * @param $value mixed The value that needs to be cached
     * @param $lifeTime int The life-time of the cache item in seconds
     *
     * @return $this
     */
    public function set( $key, $value, $lifeTime ) {

        $path = $this->getKeyPath( $key );
        $dir = dirname( $path );

        if( !is_dir( $dir ) )
            mkdir( $dir, 0777, true );

        $this->_lifeTimes[ $key ] = intval( $lifeTime );

        //Save the life times
        file_put_contents( $this->_lifeTimePath, json_encode( $this->_lifeTimes ) );

        //Save the cache content
        file_put_contents( $path, json_encode(
            $value,
            \JSON_HEX_TAG | \JSON_HEX_AMP | \JSON_HEX_APOS | \JSON_HEX_QUOT
        ) );

        return $this;
    }

    /**
     * @param $key
     *
     * @return $this
     */
    public function remove( $key ) {

        unlink( $this->getKeyPath( $key ) );

        return $this;
    }
}