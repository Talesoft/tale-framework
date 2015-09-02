<?php

namespace Tale\App\Controller\Response;

use Tale\App\Controller\Response;

class Redirect extends Response {

    public function __construct( $url ) {
        parent::__construct( 'redirect', $url );
    }
}