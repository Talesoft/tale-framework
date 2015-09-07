<?php

namespace Tale;

use RuntimeException;
use Tale\App\FeatureBase;
use Tale\Proxy;
use Tale\Util\ArrayUtil;
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
            'library'    => 'Tale\\App\\Feature\\Library',
            'cache'      => 'Tale\\App\\Feature\\Cache',
            'controller' => 'Tale\\App\\Feature\\Controller',
            'data'       => 'Tale\\App\\Feature\\Data',
            'model'      => 'Tale\\App\\Feature\\Model',
            'view'       => 'Tale\\App\\Feature\\View',
            'router'     => 'Tale\\App\\Feature\\Router',
            'form'       => 'Tale\\App\\Feature\\Form',
            'session'       => 'Tale\\App\\Feature\\Session',
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
            ini_set($option, $value);
        }
    }

    private function _registerFeatureAliases()
    {
        foreach ($this->getOption('featureAliases') as $alias => $className) {

            $this->_featureFactory->registerAlias($alias, $className);
        }
    }



    public function run(App $previousApp = null)
    {

        //Make sure all correct feature aliases are registered
        $this->_registerFeatureAliases();

        //We will sort the features by dependency on other features
        //and initialize them in the correct order

        //First we create an instance of all features.
        /**
         * @var \Tale\App\FeatureBase[] $features
         */
        $features = [];
        foreach ($this->getOption('features') as $name => $options) {

            //Aliases are resolved.
            $className = $this->_featureFactory->resolveClassName($name);

            //We don't want duplicate instances. Rather create a new feature
            //It's better for unique identification
            if (isset($features[$className]))
                throw new RuntimeException(
                    "Failed to add feature $name($className): Feature does already exist"
                );

            //This creates the actual instance via Tale\Factory
            $feature = $this->_featureFactory->createInstance($className, [$this]);

            //We can pass a string as the options to use a config file
            if (is_string($options))
                $feature->appendOptionFile($options, true);
            else if (is_array($options))
                $feature->appendOptions($options, true);
            else
                throw new RuntimeException(
                    "Failed to initialize feature $name($className): "
                    ."Config needs to be a path to a file or an option array"
                );

            $features[] = $feature;
        }

        //All features are instanced and got all the options they will get
        //at this point.
        //Now we sort the features by dependencies.
        //TODO: Circular dependencies probably fuck up.
        ArrayUtil::mergeSort($features, [__CLASS__, 'compareFeatures']);

        $classes = array_map('get_class', $features);
        $features = array_combine($classes, $features);

        //Now we can initialize the features
        //Since our deps are ordered now, we can just iterate
        foreach ($features as $feature) {

            //First append our dependencies
            foreach ($feature->getDependencies() as $name => $className) {

                if (isset($features[$className]))
                    $feature->setDependency($name, $features[$className]);
            }

            //Now initialize the feature
            //This is the possibility to add events etc.
            $feature->init();
        }


        //This is only sorting! We dont ensure, that the feature exists.
        //The feature has to do that by itself (through an easy way, look at it)

        //Put together our event args
        $args = [
            'app'         => $this,
            'previousApp' => $previousApp
        ];

        //First, we prepare (and let features prepare)
        if($this->emit('beforeRun', new Event\Args($args))) {

            $this->_setPhpOptions();

            //Then we run
            if ($this->emit('run', new Event\Args($args))) {

                //Clear our features
                $this->_features = null;

                //Allow clean-up at the end (Notice that the "true"
                //makes it reverse
                $this->emit('afterRun', new Event\Args($args), true);
            }
        }

        return $this;
    }

    public static function compareFeatures(FeatureBase $a, FeatureBase $b)
    {

        $aDeps = $a->getDependencies();
        $bDeps = $b->getDependencies();

        if(!count($aDeps) && !count($bDeps))
            return 0;

        $aClass = get_class($a);
        $bClass = get_class($b);

        if ($b->isPrioritised() || in_array($bClass, $aDeps))
            return 1;

        if ($a->isPrioritised() || in_array($aClass, $bDeps))
            return -1;

        return 0;
    }

    //Cloning this wouldn't be wise, would it?
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