<?php

namespace Tale\App\Feature;

use Tale\App\FeatureBase;

class Config extends FeatureBase {

    private $_configFiles;

    protected function init() {
        parent::init();

        $app = $this->getApp();
        $config = $this->getConfig();
        $configFiles = null;


        if( !isset( $config->files ) ) {

            $configFiles = glob( $config->path.'/*.json' );
        } else {

            $configFiles = array_map( function( $path ) use( $config ) {

                return $config->path."/$path.json";
            }, $config->files->getOptions() );
        }

        $this->_configFiles = $configFiles;

        foreach( $configFiles as $configFile )
            $app->loadConfigFile( $configFile );
    }

    public function getConfigFiles() {

        return $this->_configFiles;
    }
}