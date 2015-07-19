<?php

namespace Tale\Cache\Adapter\File\Format;

use Tale\Cache\Adapter\File\FormatBase;

class Json extends FormatBase {

    public function getExtension() {

        return '.json';
    }

    public function load( $path ) {

        return json_decode( file_get_contents( $path ), true );
    }

    public function save( $path, $value ) {

        file_put_contents( $path, json_encode(
            $value,
            \JSON_HEX_TAG | \JSON_HEX_AMP | \JSON_HEX_APOS | \JSON_HEX_QUOT
        ) );

        return $this;
    }
}