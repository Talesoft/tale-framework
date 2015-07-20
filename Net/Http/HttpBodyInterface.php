<?php

namespace Tale\Net\Http;

interface HttpBodyInterface {

    public function getContentType();
    public function setContentType( $contentType );
    public function getContentEncoding();
    public function setContentEncoding( $encoding );
    public function getContentLength();

	public function getContent();
	public function setContent( $content );

	public function getContentArray();
    public function setContentArray( array $items );

	public function clearContent();
	public function appendContent( $content );
	public function prependContent( $content );

    public function getString();
    public function __toString();
}