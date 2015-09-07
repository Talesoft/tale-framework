<?php

namespace Tale\Session;

use Tale\Config;
use Tale\Factory;

class Manager
{
    use Config\OptionalTrait;

    private static $_idCounter = 0;

    /** @var \SessionHandler */
    private $_handler;
    private $_handlerFactory;

    public function __construct(array $options = null)
    {

        $this->appendOptions([
            'handler' => 'internal',
            'options' => [],
            'handlerAliases' => [],
            'lifeTime' => 24 * 60 * 60,
            'name' => 'session-'.(self::$_idCounter++)
        ]);

        if ($options)
            $this->appendOptions($options);

        $this->_handlerFactory = new Factory('SessionHandlerInterface', [
            'internal' => 'SessionHandler'
        ]);

        foreach ($this->getOption('handlerAliases') as $alias => $className)
            $this->_handlerFactory->registerAlias($alias, $className);

        $args = [];
        $className = $this->_handlerFactory->resolveClassName($this->getOption('handler'));

        if (is_subclass_of($className, 'Tale\\Session\\HandlerBase', true))
            $args[] = $this->getOption('options');

        $this->_handler = $this->_handlerFactory->createInstance(
            $this->getOption('handler'),
            $args
        );
    }

    public function __destruct()
    {

        $this->_handler = null;
    }

    public function getId()
    {

        return session_id();
    }

    public function isStarted()
    {

        $id = $this->getId();;
        return !empty($id);
    }

    public function start()
    {

        session_set_save_handler($this->_handler, true);
        session_name($this->getOption('name'));
        session_start();

        return $this;
    }

    public function destroy()
    {

        if (!$this->isStarted())
            $this->start();

        foreach ($_SESSION as $key => $val)
            unset($_SESSION[$key]);

        session_destroy();

        return $this;
    }

    public function has($key)
    {

        if (!$this->isStarted())
            $this->start();

        return isset($_SESSION[$key]);
    }

    public function get($key, $defaultValue = null)
    {

        if (!$this->isStarted())
            return $this->start();

        if ($this->has($key))
            return $_SESSION[$key];

        return $defaultValue;
    }

    public function set($key, $value)
    {

        if (!$this->isStarted())
            $this->start();

        $_SESSION[$key] = $value;

        return $this;
    }

    public function setArray(array $data)
    {

        foreach ($data as $key => $value)
            $this->set($key, $value);

        return $this;
    }

    public function remove($key)
    {

        if (!$this->isStarted())
            $this->start();

        unset($_SESSION[$key]);

        return $this;
    }

    function __isset($name)
    {

        return $this->has($name);
    }

    function __get($name)
    {

        return $this->get($name);
    }

    function __set($name, $value)
    {

        $this->set($name, $value);
    }

    function __unset($name)
    {

        $this->remove($name);
    }
}