<?php

namespace Tale\Cache;

use InvalidArgumentException;
use Tale\Config;
use Tale\Factory;

class Manager
{
    use Config\OptionalTrait;

    private $_adapterFactory;
    private $_adapter;

    public function __construct(array $options = null)
    {

        $this->_adapterFactory = new Factory('Tale\\Cache\\AdapterBase', [
            'file'      => 'Tale\\Cache\\Adapter\\File',
            'apc'       => 'Tale\\Cache\\Adapter\\Apc',
            'memcached' => 'Tale\\Cache\\Adapter\\Memcached',
            'xcache'    => 'Tale\\Cache\\Adapter\\Xcache'
        ]);

        $this->appendOptions([
            'nameSpace'      => '',
            'lifeTime'       => 3600,
            'adapterAliases' => [],
            'adapter'        => 'file',
            'options'        => []
        ]);

        if($options)
            $this->appendOptions($options);

        foreach ($this->getOption('adapterAliases') as $alias => $className)
            $this->_adapterFactory->registerAlias($alias, $className);

        $this->_adapter = $this->_adapterFactory->createInstance($this->getOption('adapter'), [
            $this->getOption('options')
        ]);
    }

    public function getAdapterFactory()
    {

        return $this->_adapterFactory;
    }

    public function getAdapter()
    {

        return $this->_adapter;
    }

    public function createSubCache($nameSpace, array $options = null)
    {

        $subNs = !empty($this->_config->nameSpace)
            ? $this->_config->nameSpace.'.'
            : '';

        $options = $this->getConfig()->getItems();
        $config['nameSpace'] = "$subNs$nameSpace";

        return new static($options);
    }

    public function fetch($key, callable $action, $lifeTime = null)
    {

        $lifeTime = !is_null($lifeTime) ? $lifeTime : $this->_config->lifeTime;

        $key = !empty($this->_config->nameSpace)
            ? $this->_config->nameSpace.".$key"
            : $key;

        if ($this->_adapter->exists($key)) {

            return $this->_adapter->get($key);
        }

        //TODO: Maybe we need $args here?
        $result = call_user_func($action);
        $this->_adapter->set($key, $result, $lifeTime);

        return $result;
    }

    //Cloning this would be wise, would it?
    private function __clone() { }
}