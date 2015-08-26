<?php

namespace Tale\App\Feature;

use Tale\App\Controller\Dispatcher;
use Tale\App\FeatureBase;
use Tale\Proxy;

class Controllers extends FeatureBase {
    use Proxy\CallTrait;

    private $_dispatcher;

    public function run() {

        $app = $this->getApp();
        $appConfig = $app->getConfig();
        $config = $this->getConfig();

        $this->_dispatcher = new Dispatcher( $config->mergeArray(  [
            'path' => "{$appConfig}/controllers",
        ], false, true )->getItems() );

        $this->setArg( 'app', $this->getApp() );
    }

    public function getCallProxyTarget() {

        return $this->_dispatcher;
    }
}