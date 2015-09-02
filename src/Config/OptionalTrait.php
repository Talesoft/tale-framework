<?php

namespace Tale\Config;

use Tale\Collection;
use Tale\Config;

trait OptionalTrait
{

    private $_config;

    public function getConfigClassName()
    {

        return 'Tale\\Config';
    }

    /**
     * @return Config
     */
    public function getConfig()
    {

        if (!isset($this->_config)) {

            $className = $this->getConfigClassName();
            $this->_config = new $className();
        }

        return $this->_config;
    }

    public function mergeOptions(array $options, $recursive = false, $reverse = false)
    {

        $this->getConfig()->mergeArray($options, $recursive, $reverse)->interpolate();

        return $this;
    }

    public function appendOptions(array $options, $recursive = false)
    {

        return $this->mergeOptions($options, $recursive);
    }

    public function prependOptions(array $options, $recursive = false)
    {

        return $this->mergeOptions($options, $recursive);
    }

    public function mergeOptionFile($path, $recursive = false)
    {

        $this->getConfig()->merge(Collection::fromFile($path), $recursive);

        return $this;
    }

    public function hasOption($key)
    {

        return $this->getConfig()->hasItem($key);
    }

    public function getOption($key)
    {

        return $this->getConfig()->getItem($key);
    }
}