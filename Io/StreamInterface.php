<?php

namespace Tale\Io;

interface StreamInterface {

	public function getLength();
	public function getPosition();
	public function isAtEnd();

	public function read( $length );
	public function write( $bytes, $length );
	public function seek( $offset, $origin = SeekOrigin::CURRENT );

	public function isReadable();
	public function isWritable();
	public function isSeekable();
}