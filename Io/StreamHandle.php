<?php

namespace Tale\Io;

use Tale\System\Exception;

class StreamHandle {

    private $_handle;
    private $_data;

    public function __construct( $handle ) {

        if( !is_resource( $handle ) || get_resource_type( $handle ) !== 'stream' )
            throw new Exception( "Argument 1 passed to Stream::__construct needs to be PHP stream resource" );

        $this->_handle = $handle;
        $this->_data = stream_get_meta_data( $handle );
    }

    public function __destruct() {

        $this->close();
    }

    public function close() {

        if( is_resource( $this->_handle ) )
            fclose( $this->_handle );

        return $this;
    }

    public function getHandle() {

        return $this->_handle;
    }

    public function getData() {

        return $this->_data;
    }

    public function isTimedOut() {

        return $this->_data[ 'timed_out' ];
    }

    public function isBlocked() {

        return $this->_data[ 'blocked' ];
    }

    public function isAtEnd() {

        return $this->_data[ 'eof' ];
    }

    public function getUnreadByteCount() {

        return $this->_data[ 'unread_bytes' ];
    }

    public function getType() {

        return $this->_data[ 'stream_type' ];
    }

    public function getWrapperType() {

        return $this->_data[ 'wrapper_type' ];
    }

    public function getWrapperData() {

        return $this->_data[ 'wrapper_data' ];
    }

    public function getMode() {

        return $this->_data[ 'mode' ];
    }

    public function isReadable() {

        return in_array( trim( $this->_data[ 'mode' ], 'b' ), [
            StreamMode::READ,
            StreamMode::READ_WRITE,
            StreamMode::WRITE_READ,
            StreamMode::APPEND_READ,
            StreamMode::WRITE_READ_NEW,
            StreamMode::WRITE_READ_CREATE
        ], true );
    }

    public function isWritable() {

        return in_array( trim( $this->_data[ 'mode' ], 'b' ), [
            StreamMode::READ_WRITE,
            StreamMode::WRITE,
            StreamMode::WRITE_READ,
            StreamMode::APPEND,
            StreamMode::APPEND_READ,
            StreamMode::WRITE_NEW,
            StreamMode::WRITE_READ_NEW,
            StreamMode::WRITE_CREATE,
            StreamMode::WRITE_READ_CREATE
        ], true );
    }

    public function isBinary() {

        return strpos( $this->_data[ 'mode' ], 'b' ) !== false;
    }

    public function isSeekable() {

        return is_bool( $this->_data[ 'seekable' ] ) ? $this->_data[ 'seekable' ] : null;
    }

    public function getUriString() {

        return $this->_data[ 'uri' ];
    }

    public static function fromUriString( $uriString, $mode = null ) {

        $mode = $mode ? $mode : StreamMode::READ;

        return new self( fopen( $uriString, $mode ) );
    }
}