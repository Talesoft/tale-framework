<?php

namespace Tale\Dom;

use Tale\System\ArrayObject;

class DomAttributeSet extends ArrayObject {

    public function __toString() {

        if( !$this->count() )
            return '';

        $attrs = clone $this;
        return $attrs->map( function( $val, $key ) {

            return "$key=\"$val\"";
        } )->join( ' ' );
    }
}