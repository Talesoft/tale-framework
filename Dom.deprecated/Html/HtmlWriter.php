<?php

namespace Tale\Dom\Html;

use Tale\Dom\DomWriter,
    Tale\Io\StreamInterface;

class HtmlWriter extends DomWriter {

    public function __construct( StreamInterface $stream, array $options = null ) {
        parent::__construct( $stream, array_replace( [
            'selfClosingTags' => [ 'input', 'link', 'br', 'img', 'hr' ],
            'selfClosingString' => ''
        ], $options ? $options : [] ) );
    }

    public function writeDocument( HtmlDocument $document ) {

        $newLine = $this->isPretty() ? $this->getNewLine() : '';
        $indent = $this->isPretty() ? str_repeat( $this->getTabString(), $this->getLevel() ) : '';

        $this->writeText( "<!DOCTYPE ".$document->getDocumentType().">".$newLine );
        $this->writeElement( $document );
    }
}