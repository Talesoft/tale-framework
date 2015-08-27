<?php

namespace Tale;

use RuntimeException;
use Tale\Proxy;

/*
 * CONS: Apps should be queuable and there should be inter-app-communication
 *       Imagine this:
 *
 * $site = new App( './apps/site' );
 * $blog = new App( './apps/blog' );
 * $shop = new App( './apps/shop' );
 *
 * And then either:
 * $shop->run( $blog->run( $site->run() ) );
 *
 * or rather directly some kind of AppQueue
 *
 * $queue = new App\Queue( [ './apps/site', './apps/blog', './apps/shop' ] );
 * $queue->run();
 *
 * Apps should also follow a Request->Response model (Think about this)
 */

/**
 * Represents an application that can be run from any point in userland code
 *
 * An application is always based on a path where a config file and additional directories and dependencies reside
 *
 * The features are initialized in the constructor
 * As soon as you call ->run() on the app, ->run() is called on all features
 *
 * @version 1.0
 * @featureState Development
 *
 * @package Tale
 */
class App extends Config\Container {
    use Proxy\PropertyGetOffsetTrait;

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
     * @var App\FeatureBase[]
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

        $this->setDefaultOptions( [
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
    }

    private function _setPhpOptions() {

        //Iterate the whole config and set all options, regardless if we set some already
        $config = $this->getConfig();

        if( isset( $config->phpOptions ) ) {

            foreach( $config->phpOptions as $option => $value ) {

                $option = StringUtil::tableize( $option, '.' );
                ini_set( $option, $value );
            }
        }
    }

    private function _registerFeatureAliases() {

        $config = $this->getConfig();

        if( isset( $config->featureAliases ) ) {

            foreach( $config->featureAliases as $alias => $className )
                $this->_featureFactory->registerAlias( $alias, $className );
        }
    }

    private function _registerFeatures() {

        $config = $this->getConfig();

        if( isset( $config->features ) ) {

            foreach( $config->features as $name => $options )
                $this->addFeature( $name, $options );
        }
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
        $this->_registerFeatureAliases();
        $this->_registerFeatures();

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
     * @return App\FeatureBase[] An array of App\FeatureBase-objects
     */
    public function getFeatures() {

        return $this->_features;
    }

    /**
     * Adds a new feature by passing an option array
     *
     * If a feature with the same class was already created, the options of that feature will
     * be merged with the passed options and the old instance will be kept
     *
     * @param string     $name
     * @param array|null $options
     *
     * @return $this
     */
    public function addFeature( $name, array $options = null ) {

        if( isset( $this->_features[ $name ] ) ) {

            //Checks if the feature is still initializing (e.g. if the feature-init() function calls "loadConfig" or addFeature on itself
            if( !( $this->_features[ $name ] instanceof App\FeatureBase ) )
                return $this;

            //Feature was already added, we just add the new config (if needed)
            if( $options ) {

                $cfg = $this->_features[ $name ]->getConfig();

                if( !$cfg->isMutable() )
                    throw new \RuntimeException( "Failed to merge config for $name feature: Config object is not mutable" );

                $cfg->mergeArray( $options, true, true );
            }

            return $this;
        }

        //We set the feature to true for the detection above to return true if you call addFeature/loadConfig or something
        //similar INSIDE the feature (constructor or init()-method)
        $this->_features[ $name ] = true;

        //Create the actual instance
        $this->_features[ $name ] = $this->_featureFactory->createInstance( $name, [
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

        foreach( $features as $name => $options )
            $this->addFeature( $name, $options );

        return $this;
    }

    public function run() {

        var_dump( array_keys( $this->_features ) );

        foreach( $this->_features as $name => $feature ) {
            var_dump( "RUN $name" );
            $feature->run();
        }

        return $this;
    }

    public function getOffsetProxyTarget() {

        return $this->_features;
    }
}