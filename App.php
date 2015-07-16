<?php

namespace Tale;

use Exception,
    RuntimeException;

class App {

    private static $_featureTypes = [

    ];

    private $_path;
    private $_configPath;
    private $_config;
    private $_featureFactory;
    private $_features;

    public function __construct( $path) {

        $this->_path = $path;
        $this->_configPath = "$path/app.json";
        $this->_config = new Config( [
            'path' => $this->_path
        ] );
        $this->_featureFactory = new Factory( 'Tale\\App\\FeatureBase', [
            'config' => 'Tale\\App\\Feature\\Config',
            'library' => 'Tale\\App\\Feature\\Library',
            'cache' => 'Tale\\App\\Feature\\Cache',
            'controllers' => 'Tale\\App\\Feature\\Controllers',
            'db' => 'Tale\\App\\Feature\\Db',
            'themes' => 'Tale\\App\\Feature\\Themes',
            'views' => 'Tale\\App\\Feature\\Views'
        ] );
        $this->_features = [];

        if( !file_exists( $this->_configPath ) )
            throw new RuntimeException( "Failed to create app: App config {$this->_configPath} not found" );

        $this->loadConfigFile( $this->_configPath );
    }

    public function getPath() {

        return $this->_path;
    }

    public function getConfigPath() {

        return $this->_configPath;
    }

    public function getConfig() {

        return $this->_config;
    }

    public function loadConfigFile( $configFile ) {

        $config = Config::fromFile( $configFile );
        $this->_config = $this->_config->mergeConfig( $config )->interpolate();

        //Init feature types
        if( isset( $config->featureAliases ) )
            foreach( $config->featureAliases as $alias => $className )
                $this->_featureFactory->registerAlias( $alias, $this->_config->featureAliases->{$alias} );

        //Init features
        if( isset( $config->features ) ) {

            foreach( $config->features as $className => $options ) {

                $config = $this->_config->features->{$className};
                $this->addFeature($className, $config ? $config->getOptions() : null );
            }
        }

        return $this;
    }

    public function getFeatureFactory() {

        return $this->_featureFactory;
    }

    public function getFeatures() {

        return $this->_features;
    }

    public function hasFeature( $className ) {

        $className = $this->_featureFactory->resolveClassName( $className );

        return isset( $this->_features[ $className ] );
    }

    public function getFeature( $className ) {

        $className = $this->_featureFactory->resolveClassName( $className );

        return $this->_features[ $className ];
    }

    public function addFeature( $className, array $options = null ) {

        $this->_features[ $className ] = $this->_featureFactory->createInstance( $className, [
            $this,
            $options
        ] );

        return $this;
    }

    public function addFeatures( array $features ) {

        foreach( $features as $className => $options )
            $this->addFeature( $className, $options );

        return $this;
    }

    public function __get( $className ) {

        return $this->getFeature( $className );
    }
}