<?php

namespace Tale\Dispatcher;

use Tale\Dispatcher;

class Instance
{

    private $_dispatcher;
    private $_internalInstance;
    private $_reflection;
    private $_methods;

    public function __construct(Dispatcher $dispatcher, $internalInstance)
    {

        $this->_dispatcher = $dispatcher;
        $this->_internalInstance = $internalInstance;
        $this->_reflection = new \ReflectionClass($this->_internalInstance);
        $this->_methods = array_map(function ($method) {

            return $method->getName();
        }, array_filter($this->_reflection->getMethods(\ReflectionMethod::IS_PUBLIC), function ($method) {

            return !$method->isStatic();
        }));
    }

    public function __destruct()
    {

        $this->_instance = null;
    }

    /**
     * @return Dispatcher
     */
    public function getDispatcher()
    {

        return $this->_dispatcher;
    }


    public function getInternalInstance()
    {

        return $this->_internalInstance;
    }

    public function hasMethod($method)
    {

        return in_array($method, $this->_methods, true);
    }

    public function getMethods($expression = null)
    {

        if ($expression) {

            return array_filter($this->_methods, function ($method) use ($expression) {

                return preg_match("/$expression/", $method);
            });
        }

        return $this->_methods;
    }

    public function call($method, array $args = null, $resolve = true)
    {

        $method = $resolve ? $this->_dispatcher->resolveMethodName($method) : $method;
        $args = $args ? $args : [];

        if (!$this->hasMethod($method))
            throw new \RuntimeException("Failed to dispatch method $method: Method not found in ".get_class($this->_instance));

        return call_user_func_array([$this->_internalInstance, $method], $args);
    }

    public function getCallIterator($methods, array $args = null, $resolve = true)
    {

        $args = $args ? $args : [];

        if ($resolve)
            foreach ($methods as $i => $method)
                $methods[$i] = $this->_dispatcher->resolveMethodName($method);

        return new CallIterator($this->_internalInstance, $methods, $args);
    }

    public function getExpressionIterator($expression, array $args = null)
    {

        return $this->getCallIterator($this->getMethods($expression), $args, false);
    }

    public function __call($expression, array $args = null)
    {

        return $this->getExpressionIterator($expression, $args);
    }

    public function __get($expression)
    {

        return $this->__call($expression);
    }
}