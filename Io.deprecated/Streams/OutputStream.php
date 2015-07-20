<?php

namespace Tale\Io\Streams;

use Tale\Io\Stream,
    Tale\Io\StreamHandle,
    Tale\Io\StreamMode;

class OutputStream extends Stream {

    public function __construct() {
        parent::__construct( StreamHandle::fromUriString( "php://output", StreamMode::WRITE ) );
    }
}