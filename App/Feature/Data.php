<?php

namespace Tale\App\Feature;

use Tale\App\ProxyFeatureBase,
    Tale\Data\Source;

class Data extends ProxyFeatureBase {

    private $_source;

    protected function init() {

        if( !class_exists( 'Tale\\Data\\Source' ) )
            throw new \RuntimeException(
                "Failed to initialize \"data\"-feature: Tale Data Module not found. "
              . "You might need to pull tale-data submodule"
            );

        $app = $this->getApp();
        $config = $this->getConfig();

        $this->_source = new Source( $config->getOptions() );

        if( isset( $app->cache ) ) {

            $cache = $app->cache->getSubCache( 'tale.data' );
            $this->_source->setCache( $cache );
        }
    }

    public function getTarget() {

        return $this->_source;
    }
}