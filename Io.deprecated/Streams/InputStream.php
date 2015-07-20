<?php

namespace Tale\Io\Streams;

use Tale\Io\Stream,
    Tale\Io\StreamHandle,
    Tale\Io\StreamMode;

class InputStream extends Stream {

    public function __construct() {
        parent::__construct( StreamHandle::fromUriString( "php://input", StreamMode::READ ) );
    }
}