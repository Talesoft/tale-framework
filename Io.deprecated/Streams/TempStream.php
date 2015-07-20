<?php

namespace Tale\Io\Streams;

use Tale\Io\Stream,
    Tale\Io\StreamHandle,
    Tale\Io\StreamMode;

class MemoryStream extends Stream {

    const DEFAULT_SIZE = 5242880;

    private $_size;

    public function __construct( $mode = null, $size = null ) {

        $this->_size = $size ? $size : self::DEFAULT_SIZE;

        parent::__construct( StreamHandle::fromUriString( "php://temp/maxmemory:$size", $mode ), $size );
    }
}