<?php

namespace Tale\App;

use Tale\App;
use Tale\Config;
use Tale\Event;

abstract class FeatureBase
{
    use Config\OptionalTrait;
    use Event\EmitterTrait;

    private $_app;
    private $_dependencyInstances;

    public function __construct(App $app)
    {

        $this->_app = $app;
        $this->_dependencyInstances = [];
    }

    public function getApp()
    {

        return $this->_app;
    }

    public function getDependencies()
    {

        return [];
    }

    public function hasDependency($name)
    {

        return isset($this->_dependencyInstances[$name]);
    }

    public function getDependency($name, $required = false)
    {

        $exists = $this->hasDependency($name);
        if ($required && !$exists)
            throw new \RuntimeException(
                "Failed to get dependency for feature: "
                .get_class($this)." requires the $name dependency"
            );

        return $exists ? $this->_dependencyInstances[$name] : null;
    }

    public function setDependency($name, FeatureBase $instance)
    {

        $this->_dependencyInstances[$name] = $instance;

        return $this;
    }

    public function isPrioritised() {

        return false;
    }

    public function __isset($name)
    {

        return $this->hasDependency($name);
    }

    public function __get($name)
    {

        return $this->getDependency($name);
    }

    abstract public function init();
}