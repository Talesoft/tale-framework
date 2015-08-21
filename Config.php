<?php

namespace Tale;

use IteratorAggregate,
    ArrayIterator,
    Countable;

/**
 * A simple configuration wrapper respresentiv a PHP config array
 *
 * @version 1.0
 * @featureState Pending
 *
 * @package Tale
 */
class Config implements IteratorAggregate, Countable {

    /**
     * The internal config array (multi-dimensional, associative)
     * @var array
     */
    private $_options;

    /**
     * Create a new Config instance
     *
     * @param array|null $options The initial configuration (e.g. default values)
     */
    public function __construct( array $options = null ) {

        $this->_options = $options ? $options : [];
    }

    /**
     * Returns the flat option array
     *
     * @return array
     */
    public function getOptions() {

        return $this->_options;
    }

    /**
     * Merges the internal option array with another option-array.
     *
     * The method mutates, meaning the output will be a new Config-object,
     * we will not merge into the existing object
     *
     * This is useful if you have default values.
     * e.g.
     *
     * $dbConfig = new Config( [
     *      'adapter' => 'mysql',
     *      'data' => [
     *          'host' => 'localhost',
     *          'user' => 'root',
     *      ]
     * ] );
     *
     * $dbConfig = $dbConfig->merge( [
     *      'adapter' => 'mysql',
     *      'data' => [
     *          'host' => 'example.com',
     *          'password' => 'r00t'
     *      ]
     * ] );
     *
     * For non-recursive usage use the second parameter, e.g.
     *
     * $dbConfig = $dbConfig->merge( [
     *      'adapter' => 'xml',
     *      'data' => [
     *          'path' => '/path/to/xml/files'
     *      ]
     * ], false );
     *
     * @param array $options The array to merge into the current option array
     * @param bool|true $recursive Replace recursively (Usage depends on configuration-style)
     * @return static The new config object with the arrays merged
     */
    public function merge( array $options, $recursive = true ) {

        $options = $recursive
                 ? array_replace_recursive( $this->_options, $options )
                 : array_replace( $this->_options, $options );

        return new static( $options );
    }

    /**
     * Merges another Config-object into this config-object
     *
     * It basically calls ->getOptions() on the config object and passes it to ->merge()
     *
     * @param Config $config The config object to merge into the current config
     * @param bool|true $recursive Replace recursively
     * @return Config The new config object with the arrays merged
     */
    public function mergeConfig( self $config, $recursive = true ) {

        return $this->merge( $config->getOptions(), $recursive );
    }

    /**
     * Interpolates the internal config with itself.
     * For a deeper explanation, have a look at Tale\StringUtils::interpolateArray
     *
     * This method mutates the config array. If you want to keep both versions, use the "clone" keyword
     *
     * @see Tale\StringUtils::interpolateArray
     *
     * @return $this The current config object with the strings interpolated
     */
    public function interpolate() {

        StringUtils::interpolateArray( $this->_options );

        return $this;
    }

    /**
     * Returns a PHP ArrayIterator for the options in order to iterate them with foreach
     *
     * Interface: \Iterator
     *
     * @return ArrayIterator The Iterator with the options contained
     */
    public function getIterator() {

        return new ArrayIterator( $this->_options );
    }

    /**
     * Returns the amount of options in the current option array (Only the first dimension)
     *
     * Interface: \Countable
     *
     * @return int
     */
    public function count() {

        return count( $this->_options );
    }

    /**
     * Allows check for option existence via property access
     *
     * e.g.
     *
     * if( isset( $config->someOption ) )
     *      //do something with $config->someOption
     *
     * The property name equals the key name
     *
     * @param $key The key you'd like to check existence for
     * @return bool True, if the key exists, false, if not
     */
    public function __isset( $key ) {

        return isset( $this->_options[ $key ] );
    }

    /**
     * Gets a key via Property Access
     *
     * $config->someOption will access $config->_options[ 'someOption' ]
     *
     * If the value is an array, it will be converted to a Config object
     * This allows deep property access on config files
     * (e.g. mysql_connect( $this->db->connectionData->host )
     * where $this, db and connectionData will all be Config-objects)
     *
     * @param $key The key of the option you want to retrieve
     *
     * @return static|mixed The value of the config option
     */
    public function __get( $key ) {

        $value = $this->_options[ $key ];

        if( is_array( $value ) )
            return new static( $value );

        return $value;
    }

    /**
     * Loads a config from a given file name
     *
     * This method only handles JSON-files right now!
     *
     * @todo Parse the file extension and use proper imports
     *
     * json => json_decode
     * php => include
     * yml? => Tale\Yaml\Parser
     * xml => Tale\Dom\Xml\Parser
     *
     * @param $path The path of the config file to load
     * @return static The config object generated from the passed file
     */
    public static function fromFile( $path ) {

        $json = file_get_contents( $path );
        return new static( json_decode( $json, true ) );
    }
}