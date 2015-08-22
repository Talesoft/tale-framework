<?php

namespace Tale\Dom\Html;

use Tale\Dom\Parser as DomParser;

class Parser extends DomParser {

    public static function getElementClassName() {

        return __NAMESPACE__.'\\Element';
    }
}