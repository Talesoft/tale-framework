<?php

namespace Tale\App\Feature;

use Tale\App\Controller\Dispatcher;
use Tale\App\FeatureBase;
use Tale\Proxy;

/*
 * CONS: Controllers are tied really closely to the app (An app without controllers doesnt make sense)
 *       Even though, e.g. a router could be optional if you want to make an CLI application
 *       with real controllers (Call controllers via $app->dispatch( 'cli-command.to-controller-action' ))
 * TODO: Tie this to the App! Maybe we implement a good Dispatcher directly in the app
 */

class Controllers extends FeatureBase {
    use Proxy\CallTrait;

    private $_dispatcher;

    public function run() {

        $app = $this->getApp();
        $appConfig = $app->getConfig();
        $config = $this->getConfig();

        $this->_dispatcher = new Dispatcher( $config->mergeArray(  [
            'path' => "{$appConfig->path}/controllers",
        ], false, true )->getItems() );

        $this->setArg( 'app', $this->getApp() );
    }

    public function getCallProxyTarget() {

        return $this->_dispatcher;
    }
}