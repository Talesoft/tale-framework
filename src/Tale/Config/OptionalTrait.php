<?php

namespace Tale\Config;

use Tale\Collection;
use Tale\Config;
use Tale\Util\ArrayUtil;
use Tale\Util\StringUtil;

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

        $config = $this->getConfig();

        $config->mergeArray($options, $recursive, $reverse);
        $config->interpolate();

        return $this;
    }

    public function appendOptions(array $options, $recursive = false)
    {

        return $this->mergeOptions($options, $recursive);
    }

    public function prependOptions(array $options, $recursive = false)
    {

        return $this->mergeOptions($options, $recursive, true);
    }

    public function mergeOptionFile($path, $recursive = false, $reverse = false)
    {

        $this->mergeOptions(ArrayUtil::fromFile($path), $recursive, $reverse);

        return $this;
    }

    public function appendOptionFile($path, $recursive = false)
    {

        return $this->mergeOptionFile($path, $recursive);
    }

    public function prependOptionFile($path, $recursive = false)
    {

        return $this->mergeOptionFile($path, $recursive, true);
    }

    public function hasOption($key)
    {

        return $this->getConfig()->hasItem($key);
    }

    public function getOption($key)
    {
        return $this->getConfig()->getItem($key);
    }

    public function resolveOption($key, $default = null, $delimeter = null)
    {
        return StringUtil::resolve($key, $this->getConfig()->getItems(), $default, $delimeter);
    }
}