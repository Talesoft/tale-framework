<?php

namespace Tale;

/**
 * A simple configuration wrapper respresenting a PHP config array
 *
 * @version 1.0
 * @featureState Pending
 *
 * @package Tale
 */
class Config extends Collection {

    /**
     * Create a new Config instance
     *
     * @param array|null $options The initial configuration (e.g. default values)
     */
    public function __construct( array $options = null, $flags = null ) {
        parent::__construct( $options, ( $flags ? $flags : self::FLAG_MUTABLE ) | self::FLAG_PROPERTY_ACCESS );

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