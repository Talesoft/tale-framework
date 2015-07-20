<?php

namespace Tale\Dom;

class DomSelectorPseudo {

    public static function isFirstChild( $value, DomElement $element, $index ) {

        return $index !== null && $index === 0;
    }

    public static function isLastChild( $value, DomElement $element, $index ) {

        return $index !== null && $index === $element->getParent()->getChildCount() - 1;
    }

    public static function isNthChild( $value, DomElement $element, $index ) {

        return ( $index + 1 ) === intval( $value );
    }

    public static function isNot( $value, DomElement $element, $index ) {

        return !$element->matches( $value );
    }

    public static function isEven( $value, DomElement $element, $index ) {

        return $index !== null && ( $index + 1 ) % 2 === 0;
    }

    public static function isOdd( $value, DomElement $element, $index ) {

        return !self::isEven( $value, $element, $index );
    }
}