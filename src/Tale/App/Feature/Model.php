<?php

namespace Tale\App\Feature;

use Tale\App\Feature\Model\Provider;
use Tale\App\FeatureBase;
use Tale\ClassLoader;
use Tale\Data\Source;

class Model extends FeatureBase
{

    /**
     * @var \Tale\ClassLoader[]
     */
    private $_loaders;
    private $_providers;

    public function init()
    {

        if (!class_exists('Tale\\Data\\Source'))
            throw new \RuntimeException(
                "Failed to load data feature: "
                ."The data source class wasnt found. "
                ."Maybe you need the Tale\\Data namespace?"
            );

        $app = $this->getApp();

        $app->bind('beforeRun', function () use($app) {

            if (!isset($this->data))
                throw new \RuntimeException(
                    "Failed to initialize model feature: "
                    ."The data feature is required"
                );

            /**
             * @var \Tale\App\Feature\Data $data
             */
            $data = $this->data;

            $this->_loaders = [];
            $this->_providers = [];

            foreach ($this->getConfig() as $alias => $options) {

                $options = array_replace([
                    'path'              => $app->getOption('path').'/models',
                    'nameSpace'         => null,
                    'loadPattern'       => null,
                    'createLoader'      => true,
                    'source'            => null,
                    'database'          => null
                ], $options ? $options : []);

                if ($options['createLoader']) {

                    $loader = new ClassLoader($options['path'], $options['nameSpace'], $options['loadPattern']);
                    $loader->register();
                    $this->_loaders[] = $loader;
                }

                if (!$options['source'])
                    throw new \RuntimeException(
                        "Failed to set model source: No source given"
                    );

                if (!$options['database'])
                    throw new \RuntimeException(
                        "Failed to set model database: No database given"
                    );

                $provider = new Provider(
                    $data->getSource($options['source'])->{$options['database']},
                    $options['nameSpace']
                );

                $this->_providers[$alias] = $provider;

                if (isset($this->controller)) {

                    $this->controller->setArg($alias, $provider);
                }
            }
        });

        $app->bind('afterRun', function () {


            foreach ($this->_loaders as $loader)
                $loader->unregister();

            unset($this->_loaders);
            unset($this->_providers);
        });
    }

    /**
     * @return \Tale\ClassLoader[]
     */
    public function getLoaders()
    {
        return $this->_loaders;
    }

    /**
     * @return \Tale\App\Feature\Model\Provider
     */
    public function getProviders()
    {
        return $this->_providers;
    }


    public function getDependencies()
    {

        return [
            'data' => 'Tale\\App\\Feature\\Data',
            'controller' => 'Tale\\App\\Feature\\Controller'
        ];
    }
}