<?php

namespace Tale\Dom\Xml;

use Tale\Dom\Element as DomElement;

class Element extends DomElement {

    public static function getParserClassName() {

        return __NAMESPACE__.'\\Parser';
    }
}