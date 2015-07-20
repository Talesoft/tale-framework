<?php

namespace Tale\Io;

class StreamReader extends StreamWorker implements StreamReaderInterface {

    public function __construct( StreamInterface $stream ) {
        parent::__construct( $stream );

        if( !$this->isReadable() )
            throw new Exception( "Failed to create stream reader on stream: Passed stream is not readable" );
    }

    public static function createOnInput() {

        return new static( new Streams/InputStream() );
    }
}