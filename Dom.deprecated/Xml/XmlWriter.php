<?php

namespace Tale\Dom\Xml;

use Tale\Dom\DomWriter,
    Tale\Io\StreamInterface;

class XmlWriter extends DomWriter {

    public function writeDocument( XmlDocument $document ) {

        $newLine = $this->isPretty() ? $this->getNewLine() : '';
        $indent = $this->isPretty() ? str_repeat( $this->getTabString(), $this->getLevel() ) : '';

        $this->writeText( "<?xml version=\"".$document->getVersion()."\" encoding=\"".$document->getEncoding()."\"?>".$newLine );
        $this->writeElement( $document );
    }
}