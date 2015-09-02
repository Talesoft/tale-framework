<?php

namespace Tale\Dom\Xml;

use Tale\Dom\Parser as DomParser;

class Parser extends DomParser {

    public static function getElementClassName() {

        return __NAMESPACE__.'\\Element';
    }
}