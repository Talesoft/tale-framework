<?php

namespace Tale\Io;

class StreamWorker implements StreamWorkerInterface {

    private $_stream;

    public function __construct( StreamInterface $stream ) {

        $this->_stream = $stream;
    }

    public function getStream() {

        return $this->_stream;
    }

    public function __call( $streamMethod, array $args ) {

        return call_user_func_array( [ $this->_stream, $streamMethod ], $args );
    }

    public static function createOnMemory( $mode = null ) {

        $mode = $mode ? $mode : StreamMode::READ_WRITE;

        return new static( new Streams\MemoryStream( $mode ) );
    }
}