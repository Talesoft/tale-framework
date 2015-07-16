<?php

namespace Tale;

use Exception,
    RuntimeException;

class App {

    private static $_featureTypes = [
        'config' => 'Tale\\App\\Feature\\Config',
        'library' => 'Tale\\App\\Feature\\Library',
        'cache' => 'Tale\\App\\Feature\\Cache',
        'controllers' => 'Tale\\App\\Feature\\Controllers',
        'db' => 'Tale\\App\\Feature\\Db',
        'themes' => 'Tale\\App\\Feature\\Themes',
        'views' => 'Tale\\App\\Feature\\Views'
    ];

    private $_path;
    private $_configPath;
    private $_config;
    private $_features;

    public function __construct( $path) {

        $this->_path = $path;
        $this->_configPath = "$path/app.json";
        $this->_config = new Config( [
            'path' => $this->_path
        ] );
        $this->_features = [];

        if( !file_exists( $this->_configPath ) )
            throw new RuntimException( "Failed to create app: App config {$this->_configPath} not found" );

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
        if( isset( $config->featureTypes ) ) {

            foreach( $config->featureTypes as $name => $className )
                self::registerFeatureType( $name, $this->_config->featureTypes->{$name} );
        }

        //Init features
        if( isset( $config->features ) ) {

            foreach( $config->features as $type => $options ) {

                $this->addFeature( $type, $options ? $this->_config->features->{$type}->getOptions() : null );
            }
        }

        return $this;
    }

    public function getFeatures() {

        return $this->_feature;
    }

    public function hasFeature( $type ) {

        if( isset( self::$_featureTypes[ $type ] ) )
            $type = self::$_featureTypes[ $type ];

        return isset( $this->_features[ $type ] );
    }

    public function getFeature( $type ) {

        if( isset( self::$_featureTypes[ $type ] ) )
            $type = self::$_featureTypes[ $type ];

        return $this->_features[ $type ];
    }

    public function addFeature( $type, array $options = null ) {

        if( isset( self::$_featureTypes[ $type ] ) )
            $type = self::$_featureTypes[ $type ];

        if( !class_exists( $type ) || !is_a( $type, 'Tale\\App\\FeatureBase', true ) ) {

            var_dump( spl_autoload_functions() );

            throw new RuntimeException( "Failed to add app feature: $type does not exist or is not a valid class of type Tale\\App\\Feature" );
        }

        $this->_features[ $type ] = new $type( $this, $options );
    }

    public function __get( $featureType ) {

        return $this->getFeature( $featureType );
    }

    public static function registerFeatureType( $name, $className ) {

        static::$_featureTypes[ $name ] = $className;
    }
}