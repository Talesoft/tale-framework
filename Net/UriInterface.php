<?php

namespace Tale\Net;

interface UriInterface {

	public function hasScheme();
	public function getScheme();
	public function setScheme( $scheme );

	public function hasPath();
	public function getPath();
	public function setPath( $path );

	public function getString();
    public function __toString();

    public static function fromString( $string );
}