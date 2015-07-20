<?php

namespace Tale\App\Feature;

use Tale\App\ProxyFeatureBase,
    Tale\Data\Source;

class Data extends ProxyFeatureBase {

    private $_source;

    protected function init() {

        $app = $this->getApp();
        $config = $this->getConfig();

        $this->_source = new Source( $config->getOptions() );

        if( isset( $app->cache ) ) {

            $cache = $app->cache->getProxiedObject()->getSubCache( 'tale.data' );
            $this->_source->setCache( $cache );
        }
    }

    public function getTarget() {

        return $this->_source;
    }
}