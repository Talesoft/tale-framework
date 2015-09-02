<?php

namespace Tale\App;

use Tale\App;
use Tale\Config;
use Tale\Event;

abstract class FeatureBase
{
    use Config\OptionalTrait;
    use Event\OptionalTrait;

    private $_app;

    public function __construct(App $app)
    {

        $this->_app = $app;
        $this->init();
    }

    public function getApp()
    {

        return $this->_app;
    }

    abstract protected function init();
}