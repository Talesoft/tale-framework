<?php

namespace Tale\App\Feature;

use Tale\App\FeatureBase;

class Config extends FeatureBase {

    private $_configFiles;

    protected function init() {

        $app = $this->getApp();
        $appConfig = $app->getConfig();
        $config = $this->getConfig();

        $this->setDefaultOptions( [
            'path' => "{$appConfig->path}/cache"
        ] );

        //The additional configuration files need to be merged into the app before the features are ran
        $configFiles = glob( $config->path.'/*.json' );

        if( isset( $config->order ) ) {

            //Sort the found config files by the order-array that is defined
            usort( $configFiles, [ $this, '_sort' ] );
        }

        $this->_configFiles = $configFiles;

        foreach( $configFiles as $configFile )
            $app->loadConfigFile( $configFile );
    }

    private function _sort( $a, $b ) {

        $order = $this->getConfig()->order;

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
}