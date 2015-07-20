<?php

namespace Tale\Io\Streams;

use Tale\Io\Stream,
    Tale\Io\StreamHandle,
    Tale\Io\StreamMode;

class MemoryStream extends Stream {

    public function __construct( $mode = null ) {
        parent::__construct( StreamHandle::fromUriString( "php://memory", $mode ) );
    }

    public static function fromString( $string, $mode = null ) {

        $ms = new static( $mode );
        $ms->write( $string, strlen( $string ) );
        $ms->seekStart();

        return $ms;
    }
}