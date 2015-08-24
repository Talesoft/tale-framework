<?php

namespace Tale;

/**
 * A simple configuration wrapper respresentiv a PHP config array
 *
 * @version 1.0
 * @featureState Pending
 *
 * @package Tale
 */
class Config extends ArrayObject {

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
    public function __construct( array $options = null, $flags = null ) {
        parent::__construct( $options, $flags );

    }

    public function getItem( $key ) {

        $item = parent::getItem( $key );

        if( is_array( $item ) )
            return new static( $item, $this->getFlags() );

        return $item;
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

        $items = $this->getItems();
        StringUtils::interpolateArray( $items );
        $this->setItems( $items );

        return $this;
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
     * @param string $path The path of the config file to load
     * @return static The config object generated from the passed file
     */
    public static function fromFile( $path ) {

        $json = file_get_contents( $path );
        return new static( json_decode( $json, true ) );
    }
}