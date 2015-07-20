<?php

namespace Tale\Net;

interface UrnInterface extends UriInterface {

	public function getPathArray();
	public function setPathArray( array $items );
}