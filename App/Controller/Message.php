<?php

namespace Tale\App\Controller;

class Message {

    private $_format;

    public function __construct( $format ) {

        $this->_format = strtolower( $format );
    }

    /**
     * @return mixed
     */
    public function getFormat() {

        return $this->_format;
    }
}