<?php

namespace Tale\Io;

use Tale\System\Exception;

class Stream implements StreamInterface {

    private $_blocked;
    private $_handle;
    private $_length;

    public function __construct( StreamHandle $handle, $length = null ) {

        $this->_handle = $handle;
        $this->_length = is_null( $length ) ? $length : intval( $length );
    }

    public function __destruct() {

        $this->close();
    }

    public function getHandle() {

        return $this->_handle;
    }

    public function close() {

        $this->_handle->close();

        return $this;
    }

    public function getLength() {

        return $this->_length;
    }

    public function setReadBufferSize( $bufferSize ) {

        stream_set_read_buffer( $this->_handle->getHandle(), $bufferSize );

        return $this;
    }

    public function setWriteBufferSize( $bufferSize ) {

        stream_set_write_buffer( $this->_handle->getHandle(), $bufferSize );

        return $this;
    }

    public function block() {

        stream_set_blocking( $this->_handle->getHandle(), 1 );

        return $this;
    }

    public function unblock() {

        stream_set_blocking( $this->_handle->getHandle(), 0 );

        return $this;
    }

    public function getPosition() {

        return ftell( $this->_handle->getHandle() );
    }

    public function isAtEnd() {

        return feof( $this->_handle->getHandle() );
    }

    public function write( $bytes, $length ) {

        if( !$this->isWritable() )
            throw new Exception( "Failed to write to stream: Stream is not writable" );

        return fwrite( $this->_handle->getHandle(), $bytes, $length );
    }

    public function read( $length ) {

        if( !$this->isReadable() )
            throw new Exception( "Failed to read from stream: Stream is not readable" );

        if( $this->isAtEnd() )
            return null;

        return fread( $this->_handle->getHandle(), $length );
    }

    public function seek( $offset, $origin = null ) {

        if( !$this->isSeekable() )
            throw new Exception( "Failed to write to seek stream: Stream is not seekable" );

        $origin = is_null( $origin ) ? self::ORIGIN_CURRENT : $origin;

        return fseek( $this->_handle->getHandle(), $offset, $origin ) !== -1;
    }

    public function seekStart( $offset = 0) {

        return $this->seek( $offset, SeekOrigin::START );
    }

    public function seekEnd( $offset = 0 ) {

        return $this->seek( $offset, SeekOrigin::END );
    }

    public function copyTo( StreamInterface $target, $origin = null, $bufferSize = 8192 ) {

        if( $origin !== null )
            $this->seek( 0, $origin );

        while( $buf = $this->read( $bufferSize ) )
            $target->write( $buf, strlen( $buf ) );

        return $this;
    }

    public function isReadable() {

        return $this->_handle->isReadable();
    }

    public function isWritable() {

        return $this->_handle->isWritable();
    }

    public function isSeekable() {

        return $this->_handle->isSeekable() === null ? ( $this->getLength() !== null ) : $this->_handle->isSeekable();
    }
}

