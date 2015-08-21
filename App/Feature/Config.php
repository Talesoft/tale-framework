<?php

namespace Tale\App\Feature;

use Tale\App\FeatureBase;

class Config extends FeatureBase {

    private $_configFiles;

    protected function init() {

        $app = $this->getApp();
        $config = $this->getConfig();

        if( !isset( $config->path ) )
            throw new \Exception( "The Config app-feature needs a valid {{path}} to work on" );

        $configFiles = glob( $config->path.'/*.json' );

        if( isset( $config->order ) ) {

            usort( $configFiles, [ $this, '_sort' ] );
        }

        $this->_configFiles = $configFiles;

        foreach( $configFiles as $configFile )
            $app->loadConfigFile( $configFile );
    }

    private function _sort( $a, $b ) {

        $order = $this->getConfig()->order->getOptions();

        $abn = basename( $a, '.json' );
        $bbn = basename( $b, '.json' );

        $ao = array_search( $abn, $order );
        $bo = array_search( $bbn, $order );

        if( $ao === false && $bo === false )
            return strcmp( $a, $b );

        if( $ao === false )
            return -1;

        if( $bo === false )
            return 1;

        return $ao < $bo ? -1 : 1;
    }

    public function getConfigFiles() {

        return $this->_configFiles;
    }
}