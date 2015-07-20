<?php

namespace Tale\Net;

interface CredentialInterface {

	public function hasUserName();
	public function getUserName();
	public function hasPassword();
	public function getPassword();

    public function getString();

    public function __toString();

    public static function fromString( $string );
}