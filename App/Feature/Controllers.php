<?php

namespace Tale\App\Feature;

use Tale\App\Controller\Dispatcher;
use Tale\App\ProxyFeatureBase;

class Controllers extends ProxyFeatureBase {

    private $_dispatcher;

    public function run() {

        $config = $this->getConfig();
        $this->_dispatcher = new Dispatcher( $config->mergeArray(  [
            'path' => $this->getApp()->getConfig()->path.'/controllers',
        ], false, true )->getItems() );

        $this->setArg( 'app', $this->getApp() );
    }

    public function getTarget() {

        return $this->_dispatcher;
    }
}