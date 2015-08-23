<?php

namespace Tale\App\Feature;

use Tale\App\Controller\Dispatcher;
use Tale\StringUtils;
use Tale\App\ProxyFeatureBase;

class Controllers extends ProxyFeatureBase {

    private $_dispatcher;

    protected function init() {

        $config = $this->getConfig();
        $this->_dispatcher = new Dispatcher( array_replace( [
            'path' => $this->getApp()->getConfig()->path.'/controllers',
        ], $config->getOptions() ) );

        $this->setArg( 'app', $this->getApp() );
    }

    public function getTarget() {

        return $this->_dispatcher;
    }
}