<?php

namespace Tale\Dom\Html;

use Tale\Dom\DomElement;

class HtmlElement extends DomElement {

    public function hasCss() {

        return $this->hasAttribute( 'style' );
    }

    public function getCss() {

        if( !$this->hasAttribute( 'style' ) )
            return [];

        $result = [];
        $parts = array_map( 'trim', explode( ';', $this->getAttribute( 'style' ) ) );
        foreach( $parts as $part ) {

            if( empty( $part ) )
                continue;
            
            list( $property, $value ) = explode( ':', $part );
            $result[ trim( $property ) ] = trim( $value );
        }

        return $result;
    }

    public function setCss( array $css, $merge = true ) {

        if( $merge ) 
            $css = array_replace( $this->getCss(), $css );
        
        $this->setAttribute( 'style', implode( ' ', array_map( function( $property, $value ) {

            return "$property: $value;";
        }, array_keys( $css ), $css ) ) );

        return $this;
    }

    public static function getReaderClassName() {

        return __NAMESPACE__.'\\HtmlReader';
    }

    public static function getWriterClassName() {

        return __NAMESPACE__.'\\HtmlWriter';
    }
}