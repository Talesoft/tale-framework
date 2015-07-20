<?php

namespace Tale\Dom\Html;

use Tale\Dom\DomReader,
    Tale\Io\StreamInterface;

class HtmlReader extends DomReader {

    public function __construct( StreamInterface $stream, array $options = null ) {
        parent::__construct( $stream, array_replace( [
            'elementClassName' => __NAMESPACE__.'\\HtmlElement'
        ], $options ? $options : [] ) );
    }

    public function readDocument( HtmlDocument $document ) {

        //TODO: Parse doctype and then $this->readElement()
    }
}