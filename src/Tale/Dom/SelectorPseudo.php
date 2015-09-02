<?php

namespace Tale\Dom;

class SelectorPseudo {

    public static function isFirstChild( $value, Element $element, $index ) {

        return $index !== null && $index === 0;
    }

    public static function isLastChild( $value, Element $element, $index ) {

        return $index !== null && $index === $element->getParent()->getChildCount() - 1;
    }

    public static function isNthChild( $value, Element $element, $index ) {

        return ( $index + 1 ) === intval( $value );
    }

    public static function isNot( $value, Element $element, $index ) {

        return !$element->matches( $value );
    }

    public static function isEven( $value, Element $element, $index ) {

        return $index !== null && ( $index + 1 ) % 2 === 0;
    }

    public static function isOdd( $value, Element $element, $index ) {

        return !self::isEven( $value, $element, $index );
    }
}