<?php

namespace Tale\App\Feature;

use Tale\App\FeatureBase;
use Tale\Proxy,
    Tale\Data\Source;

class Data extends FeatureBase {
    use Proxy\CallTrait;

    private $_source;

    public function run() {

        if( !class_exists( 'Tale\\Data\\Source' ) )
            throw new \RuntimeException(
                "Failed to initialize \"data\"-feature: Tale Data Module not found. "
              . "You might need to pull tale-data submodule"
            );

        $app = $this->getApp();
        $config = $this->getConfig();

        $this->_source = new Source( $config->getItems() );

        if( isset( $app->cache ) ) {

            $cache = $app->cache->createSubCache( 'data' );
            $this->_source->setSubCache( $cache );
        }
    }

    public function getCallProxyTarget() {

        return $this->_source;
    }
}