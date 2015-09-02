<?php

namespace Tale\Net;

use Tale\StringUtil;

class Credential {

    private $_userName;
    private $_password;

    public function __construct( $userName = null, $password = null ) {

        $this->_userName = $userName;
        $this->_password = $password;
    }

    public function hasUserName() {

        return !is_null( $this->_userName );
    }

    public function getUserName() {

        return $this->_userName;
    }

    public function hasPassword() {

        return !is_null( $this->_password );
    }

    public function getPassword() {

        return $this->_password;
    }

    public function getString() {

        if( !$this->_userName )
            return '';

        $password = $this->_password ? ":{$this->_password}" : '';

        return "{$this->_userName}$password";
    }

    public function __toString() {

        return $this->getString();
    }

    public static function fromString( $string ) {

        $parts = StringUtil::mapReverse( $string, ':', [ 'password', 'userName' ] );

        return new static( $parts[ 'userName' ], $parts[ 'password' ] );
    }
}