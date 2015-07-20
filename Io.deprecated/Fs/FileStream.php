<?php

namespace Tale\Io\Fs;

use Tale\Io\Stream,
    Tale\Io\StreamHandle;

class FileStream extends Stream {

    public function __construct( $path, $mode = null ) {
        parent::__construct( StreamHandle::fromUriString( $path, $mode ) );
    }
}