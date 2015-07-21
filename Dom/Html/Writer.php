<?php

namespace Tale\Dom\Html;

use Tale\Dom\Writer as DomWriter,
    Tale\Dom\Leaf;

class Writer extends DomWriter {

    public function __construct( array $options = null ) {
        parent::__construct( array_replace( [
            'selfClosingTags' => [ 'input', 'link', 'br', 'img', 'hr' ],
            'selfClosingString' => ''
        ], $options ? $options : [] ) );
    }

    public function writeLeaf( Leaf $leaf, $level = null ) {

        $newLine = $this->isPretty() ? $this->getNewLine() : '';

        $str = '';
        if( $leaf instanceof Document ) {

            $str .= "<!DOCTYPE ".$leaf->getDocumentType().">".$newLine;
        }

        return $str.parent::writeLeaf( $leaf );
    }

    public static function getElementClassName() {

        return __NAMESPACE__.'\\Element';
    }
}