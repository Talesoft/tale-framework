<?php

namespace Tale\Net;

//Notice that the Credential interface fits in here neatly.
//I don't want to extend it, though, since the toString implementation of this one 
//won't return usable credential strings (but rather, URLs!)
interface UrlInterface extends UriInterface {

	public function hasUserName();
	public function getUserName();
	public function setUserName( $name );

	public function hasPassword();
	public function getPassword();
	public function setPassword( $password );

	public function hasDomain();
	public function getDomain();
	public function setDomain( $domain );

	public function hasPort();
	public function getPort();
	public function setPort( $port );

	public function hasQueryString();
	public function getQueryString();
	public function setQueryString( $queryString );

	public function getQueryArray();
	public function setQueryArray( array $items );

	public function hasFragment();
	public function getFragment();
	public function setFragment( $fragment );
}