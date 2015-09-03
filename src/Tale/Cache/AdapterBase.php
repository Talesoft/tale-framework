<?php

namespace Tale\Cache;

use Tale\Config;

abstract class AdapterBase
{
    use Config\OptionalTrait;

    public function __construct(array $options = null)
    {

        $this->appendOptions($options);
        $this->init();
    }

    abstract protected function init();

    abstract public function exists($key);
    abstract public function get($key);
    abstract public function set($key, $value, $lifeTime);
    abstract public function remove($key);
}