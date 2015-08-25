<?php

namespace Tale;

use RuntimeException;

/**
 * Represents an application that can be run from any point in userland code
 *
 * An application is always based on a path where a config file and additional directories and dependencies reside
 *
 * @version 1.0
 * @featureState Development
 *
 * @package Tale
 */
class App extends Config\Container {

    /**
     * The path to the application directory
     *
     * @var string
     */
    private $_path;

    /**
     * The path to the config file
     *
     * @var string
     */
    private $_configPath;

    /**
     * The factory for app features
     * Creates new instances of different App\FeatureBase derived classes
     *
     * @var Factory
     */
    private $_featureFactory;

    /**
     * The features currently loaded into the application
     * These can be passed to objects that want to work with the app features with their respective aliases
     *
     * @var array
     */
    private $_features;
  
    /**
     * Creates a new App object
     *
     * @param string $path The path to the application directory
     */
    public function __construct( $path ) {
        parent::__construct();

        $this->_path = $path;
        $this->_configPath = "$path/app.json";

        $this->_featureFactory = new Factory(
            'Tale\\App\\FeatureBase', [
            'config'      => 'Tale\\App\\Feature\\Config',
            'library'     => 'Tale\\App\\Feature\\Library',
            'cache'       => 'Tale\\App\\Feature\\Cache',
            'data'        => 'Tale\\App\\Feature\\Data',
            'controllers' => 'Tale\\App\\Feature\\Controllers',
            'themes'      => 'Tale\\App\\Feature\\Themes',
            'views'       => 'Tale\\App\\Feature\\Views',
            'router'      => 'Tale\\App\\Feature\\Router'
        ] );

        $this->_features = [];

        if( !file_exists( $this->_configPath ) )
            throw new RuntimeException( "Failed to create app: App config {$this->_configPath} not found" );

        $this->setDefaultConfig( [
             //The key "path" leading to the app path is fed first to the config. This way config strings can
             //interpolate the config path via {{path}} and use it for own paths
             'path' => $this->_path,

             //We also need some pre-defined values for PHP Options to avoid unnecessary CONST parsing
             'errorLevels' => [
                 'all' => E_ALL | E_STRICT,
                 'errors' => E_NOTICE | E_WARNING | E_ERROR,
                 'warnings' => E_NOTICE | E_WARNING,
                 'notices' => E_NOTICE,
                 'none' => 0
             ]
        ] );

        $this->loadConfigFile( $this->_configPath );
        $this->_runFeatures();
    }

    /**
     * Returns the application directory path
     *
     * @return string
     */
    public function getPath() {

        return $this->_path;
    }

    public function loadConfigFile( $path ) {
        parent::loadConfigFile( $path );

        $this->_setPhpOptions();
        $this->_registerNewFeatureAliases();
        $this->_registerNewFeatures();

        return $this;
    }

    /**
     * Returns the path to the application's main configuration file
     *
     * @return string
     */
    public function getConfigPath() {

        return $this->_configPath;
    }

    /**
     * Loads a new config file by its full path
     *
     * @param string $configFile The path to the config-file to be loaded
     *
     * @return $this
     */
    /*public function loadConfigFile( $configFile ) {

        $config = Config::fromFile( $configFile );
        $this->_config = $this->_config->merge( $config )->interpolate();

        //Init php.ini settings
        if( isset( $config->phpOptions ) ) {

            foreach( $config->phpOptions as $name => $value )
                ini_set( StringUtils::tableize( $name, '.' ), $this->_config->phpOptions->{$name} );
        }

        //Init feature types
        if( isset( $config->featureAliases ) )
            foreach( $config->featureAliases as $alias => $className )
                $this->_featureFactory->registerAlias( $alias, $this->_config->featureAliases->{$alias} );

        //Init features
        if( isset( $config->features ) ) {

            foreach( $config->features as $className => $options ) {

                $config = $this->_config->features->{$className};
                $this->addFeature( $className, $config ? $config->getItems() : null );
            }
        }

        return $this;
    }*/

    /**
     * Returns the current feature factory of the app
     *
     * @return Factory
     */
    public function getFeatureFactory() {

        return $this->_featureFactory;
    }

    /**
     * Returns the current attached features of the app
     *
     * @return array An array of App\Feature-objects
     */
    public function getFeatures() {

        return $this->_features;
    }

    /**
     * Checks if a given feature is loaded or not
     *
     * @param string $className The class name or the alias of the class (aliases reside in the feature factory)
     *
     * @return bool
     */
    public function hasFeature( $className ) {

        $className = $this->_featureFactory->resolveClassName( $className );

        return isset( $this->_features[ $className ] );
    }

    /**
     * Gets the instance of a given feature
     *
     * @param string $className The class name or the alias of the class (aliases reside in the feature factory)
     *
     * @return App\FeatureBase
     */
    public function getFeature( $className ) {

        $className = $this->_featureFactory->resolveClassName( $className );

        return $this->_features[ $className ];
    }

    /**
     * Adds a new feature by passing an option array
     *
     * @param string     $className The class name or the alias of the class
     *                              (aliases reside in the feature factory)
     * @param array|null $options
     *
     * @return $this
     */
    public function addFeature( $className, array $options = null ) {

        $className = $this->_featureFactory->resolveClassName( $className );

        $this->_features[ $className ] = $this->_featureFactory->createInstance( $className, [
            $this,
            $options
        ] );

        return $this;
    }

    /**
     * Adds new features by passing an array of feature definitions
     *
     * @param array $features An array consisting of class names/aliases as keys and options as values
     *
     * @return $this
     */
    public function addFeatures( array $features ) {

        foreach( $features as $className => $options )
            $this->addFeature( $className, $options );

        return $this;
    }

    /**
     * Magic access method for isset/empty
     * Uses $this->hasFeature( $className ) for resolving
     *
     * @param string $className The class name or the alias of the class
     *                          (aliases reside in the feature factory)
     *
     * @return bool
     */
    public function __isset( $className ) {

        return $this->hasFeature( $className );
    }

    /**
     * Magic access method for property read access
     * Uses $this->getFeature( $className ) for resolving
     *
     * @param string $className The class name or the alias of the class
     *                          (aliases reside in the feature factory)
     *
     * @return App\FeatureBase
     */
    public function __get( $className ) {

        return $this->getFeature( $className );
    }
}