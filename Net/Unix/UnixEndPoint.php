<?php

namespace Tale\Net\Unix;

use Tale\Net\EndPoint,
    Tale\Net\AddressFamily,
    Tale\System\StringUtils,
    Tale\System\Exception;

class UnixEndPoint extends EndPoint {

    private $_path;

    public function __construct( $path ) {
        parent::__construct( AddressFamily::UNIX );
        
        $this->_path = $path;
    }

    public function getPath() {

        return $this->_path;
    }
}