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


    const TYPE_CLI = 'cli';
    const TYPE_WEB = 'web';
    const TYPE_SERVER = 'server';

    /**
     * The factory for app features
     * Creates new instances of different App\FeatureBase derived classes
     *
     * @var Factory
     */
    private $_featureFactory;

    /**
     * @var \Tale\App\FeatureBase[]|null
     */
    private $_features;

    /**
     * Creates a new App object
     *
     * @param array $options
     */
    public function __construct(array $options = null)
    {

        //The default feature aliases
        //These are the features you can load via the "features"-array
        //in the config
        //Add new features with
        // $this->getFeatureFactory()->registerAlias($alias, $className)
        $this->_featureFactory = new Factory(
            'Tale\\App\\FeatureBase', [
            'cache'      => 'Tale\\App\\Feature\\Cache',
            'controller' => 'Tale\\App\\Feature\\Controller',
            'data'       => 'Tale\\App\\Feature\\Data',
            'model'      => 'Tale\\App\\Feature\\Model',
            'view'       => 'Tale\\App\\Feature\\View',
            'router'     => 'Tale\\App\\Feature\\Router',
            'logger'     => 'Tale\\App\\Feature\\Logger'
        ]);

        //Set default options
        //Notice that Config is of type App\Manifest
        //App\Manifest also has a bunch of default properties
        $this->appendOptions([

            //The key "path" leading to the app path is fed first to the config. This way config strings can
            //interpolate the config path via {{path}} and use it for own paths
            'path'           => './app',
            'manifestName'   => 'app.json',

            //We also need some pre-defined values for PHP Options to avoid unnecessary CONST parsing
            'errorLevels'    => [
                'all'      => E_ALL | E_STRICT,
                'errors'   => E_NOTICE | E_WARNING | E_ERROR,
                'warnings' => E_NOTICE | E_WARNING,
                'notices'  => E_NOTICE,
                'none'     => 0
            ],

            'type'           => self::getTypeFromPhpSapiName(),

            'phpOptions'     => [],

            'featureAliases' => [],
            'features'       => [],

            'configure'      => [
                'path'    => '{{path}}/config', //See what i did there? :)
                'include' => []
            ]
        ]);

        $this->_features = null;

        //Set the user options (passed via the $options constructor argument
        $this->appendOptions($options, true);

        //Initialize the manifest (typically, app.json)
        $this->_initManifest();
        //Load additional config files via the "configure" option
        $this->_initConfig();

        var_dump('APPCFG', $this->getConfig()->getItems());
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

    public function getConfigClassName()
    {

        return 'Tale\\App\\Manifest';
    }

    public function isCliApp()
    {

        return $this->getOption('type') === self::TYPE_CLI;
    }

    public function isServerApp()
    {

        return $this->getOption('type') === self::TYPE_SERVER;
    }

    public function isWebApp()
    {

        return $this->getOption('type') === self::TYPE_WEB;
    }

    private function _initManifest()
    {

        $manifestPath = $this->getOption('path').'/'.$this->getOption('manifestName');

        if (!file_exists($manifestPath))
            throw new RuntimeException("Failed to create app: App config {$manifestPath} not found");

        $this->appendOptionFile($manifestPath, true);
    }

    private function _initConfig()
    {

        //Additional configuration
        $configPath = $this->resolveOption('configure.path');
        $includes = $this->resolveOption('configure.include');

        foreach ($includes as $pattern) {

            $path = StringUtil::joinPath($configPath, $pattern);

            $configFiles = glob($path);

            foreach ($configFiles as $configFile) {

                $this->appendOptionFile($configFile);
            }
        }
    }

    private function _setPhpOptions()
    {
        foreach ($this->getOption('phpOptions') as $option => $value) {

            $option = StringUtil::tableize($option, '.');
            var_dump("OPT $option => $value");
            ini_set($option, $value);
        }
    }

    private function _registerFeatureAliases()
    {
        foreach ($this->getOption('featureAliases') as $alias => $className) {

            var_dump("ALIAS $alias => $className");
            $this->_featureFactory->registerAlias($alias, $className);
        }
    }

    public function getFirstFeatureOfType($className)
    {

        if ($this->_features === null)
            throw new RuntimeException("Failed to check features: Features only exist while the app is ran");

        foreach ($this->_features as $feature)
            if (is_a($feature, $className, true))
                return $feature;

        return null;
    }

    public function getAllFeaturesOfType($className)
    {
        if ($this->_features === null)
            throw new RuntimeException("Failed to check features: Features only exist while the app is ran");

        $features = [];
        foreach ($this->_features as $feature)
            if (is_a($feature, $className, true))
                $features[] = $feature;

        return $features;
    }

    public function run(App $previousApp = null)
    {
        $args = [
            'app'         => $this,
            'previousApp' => $previousApp
        ];

        if ($this->emit('run', new Event\Args($args))) {

            $this->_setPhpOptions();
            $this->_registerFeatureAliases();

            $this->_features = [];
            foreach ($this->getOption('features') as $name => $options) {

                $feature = $this->_featureFactory->createInstance($name, [$this]);

                if (is_string($options))
                    $feature->appendOptionFile($options, true);
                else if (is_array($options))
                    $feature->appendOptions($options, true);
                else
                    throw new RuntimeException(
                        "Failed to initialize feature $name: "
                        ."Config needs to be a path to a file or an option array"
                    );

                $this->_features[] = $feature;
            }

            //Load features
            foreach ($this->_features as $feature)
                $feature->emit('load');


            //Run features
            foreach ($this->_features as $feature) {

                if ($feature->emit('beforeRun') && $feature->emit('run')) {

                    $feature->emit('afterRun');
                }
            }

            //Unload features
            foreach (array_reverse($this->_features) as $feature)
                $feature->emit('unload');

            $this->_features = null;

            $this->emit('afterRun', new Event\Args($args));
        }

        return $this;
    }

    //Cloning this would be wise, would it?
    private function __clone() { }

    public static function getTypeFromPhpSapiName()
    {

        $type = \PHP_SAPI;
        switch ($type) {
            case 'cli':
                return self::TYPE_CLI;
            case 'cli-server':
                return self::TYPE_SERVER;
        }

        return self::TYPE_WEB;
    }
}