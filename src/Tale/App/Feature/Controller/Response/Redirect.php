<?php

namespace Tale\App\Feature\Controller\Response;

use Tale\App\Feature\Controller\Response;

class Redirect extends Response
{

    public function __construct($url)
    {
        parent::__construct('redirect', $url);
    }
}