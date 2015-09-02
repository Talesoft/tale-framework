<?php

namespace Tale\Jade\Nodes;

class Text extends Node {
    public $value = '';

    public function __construct($line) {
        if (is_string($line)) {
            $this->value = $line;
        }
    }
}
