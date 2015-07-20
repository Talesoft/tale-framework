<?php

namespace Tale\Io;

use Tale\System\Exception;

class StreamWriter extends StreamWorker implements StreamWriterInterface {

    public function __construct( StreamInterface $stream ) {
        parent::__construct( $stream );

        if( !$this->isWritable() )
            throw new Exception( "Failed to create stream writer on stream: Passed stream is not writable" );
    }

    public static function createOnOutput() {

        return new static( new Streams/OutputStream() );
    }
}