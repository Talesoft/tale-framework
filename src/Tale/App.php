<?php

namespace Tale;

use RuntimeException;
use Tale\Proxy;
use Tale\Util\StringUtil;

/**
 * Represents an application that can be run from any point in userland code
 *
 * An application is always based on a path where a config file and additional directories and dependencies reside
 *
 * The features are initialized in the constructor
 * As soon as you call ->run() on the app, ->run() is called on all features
 *
 * @version 1.0
 * @stability Development
 *
 * @package Tale
 */
class App
{
    use Config\OptionalTrait {
        mergeOptions as private _mergeOptions;
    }
    use Event\OptionalTrait;

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
     * @param array $options
     */
    public function __construct(array $options = null)
    {

        $this->_featureFactory = new Factory(
            'Tale\\App\\FeatureBase', [
            'config'      => 'Tale\\App\\Feature\\Config',
            'cache'       => 'Tale\\App\\Feature\\Cache',
            'controllers' => 'Tale\\App\\Feature\\Controller',
            'data'        => 'Tale\\App\\Feature\\Data',
            'model'        => 'Tale\\App\\Feature\\Model',
            'views'       => 'Tale\\App\\Feature\\View',
            'router'      => 'Tale\\App\\Feature\\Router',
            'logger'      => 'Tale\\App\\Feature\\Logger'
        ]);
        $this->_features = [];

        $this->appendOptions([
            //The key "path" leading to the app path is fed first to the config. This way config strings can
            //interpolate the config path via {{path}} and use it for own paths
            'path'         => './app',
            'manifestName' => 'app.json',

            //We also need some pre-defined values for PHP Options to avoid unnecessary CONST parsing
            'errorLevels' => [
                'all'      => E_ALL | E_STRICT,
                'errors'   => E_NOTICE | E_WARNING | E_ERROR,
                'warnings' => E_NOTICE | E_WARNING,
                'notices'  => E_NOTICE,
                'none'     => 0
            ],

            'phpOptions' => [],

            'featureAliases' => [],
            'features' => []
        ]);

        $this->appendOptions($options, true);

        $manifestPath = $this->getOption('path').'/'.$this->getOption('manifestName');

        if (!file_exists($manifestPath))
            throw new RuntimeException("Failed to create app: App config {$this->_configPath} not found");

        $this->appendOptionFile($manifestPath);
    }

    /**
     * Returns the current feature factory of the app
     *
     * @return Factory
     */
    public function getFeatureFactory()
    {

        return $this->_featureFactory;
    }

    /**
     * Returns the current attached features of the app
     *
     * @return App\FeatureBase[] An array of App\FeatureBase-objects
     */
    public function getFeatures()
    {

        return $this->_features;
    }

    public function getConfigClassName()
    {

        return 'Tale\\App\\Manifest';
    }

    public function mergeOptions(array $options)
    {
        var_dump('ADD OPTIONS', $options);

        //Append any PHP options found in that config file
        if(isset($options['phpOptions'])) {

            foreach($options['phpOptions'] as $option => $value) {

                $option = StringUtil::tableize($option, '.');
                ini_set($option, $value);
            }
        }

        //Set any features aliases found
        if(isset($options['featureAliases'])) {

            foreach($options['featureAliases'] as $alias => $className)
                $this->_featureFactory->registerAlias($alias, $className);
        }

        //Initialize all features found
        if(isset($options['features'])) {

            foreach($options['features'] as $name => $options) {

                if(!isset($this->_features[$name])) {

                    $this->_features[$name] = $this->_featureFactory->createInstance($name, [$this]);

                    if($options)
                        $this->_features[$name]->appendOptions($options);

                    $this->_features[$name]->emit('init');
                    
                } else if($options)
                    $this->_features[$name]->appendOptions($options);
            }
        }

        return $this->_mergeOptions($options);
    }

    public function isCommandLine() {

        return PHP_SAPI !== 'cli';
    }

    public function isServer() {

        return PHP_SAPI !== 'cli-server';
    }

    public function isWeb() {

        return !$this->isCommandLine() && !$this->isServer();
    }

    public function run() {

        foreach($this->_features as $feature)
            $feature->run();

        return $this;
    }
}