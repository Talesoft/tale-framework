<?php

namespace Tale\App\Feature;

use Tale\App\Feature\Controller\Request;
use Tale\App\FeatureBase;
use Tale\Dispatcher;
use Tale\ClassLoader;
use Tale\Factory;

class Controller extends FeatureBase
{

    /**
     * @var \Tale\ClassLoader
     */
    private $_loader;

    /**
     * @var \Tale\Factory
     */
    private $_factory;

    /**
     * @var \Tale\Dispatcher
     */
    private $_dispatcher;

    /**
     * @var \Tale\Dispatcher\Instance[]
     */
    private $_instances;

    /**
     * @var array
     */
    private $_args;

    /**
     * @var array
     */
    private $_helpers;

    public function init()
    {

        $app = $this->getApp();

        $this->prependOptions([
            'path'              => $app->getOption('path').'/controllers',
            'nameSpace'         => null,
            'loadPattern'       => null,
            'classNamePattern'  => '%sController',
            'methodNamePattern' => '%sAction',
            'args'              => [],
            'helpers'           => [],
            'createLoader'      => true,
            'errorController'   => 'error'
        ]);

        $app->bind('beforeRun', function () {

            $this->_initLoader();
            $this->_initFactory();
            $this->_initDispatcher();

            $this->_instances = [];

            $this->_args = $this->getOption('args');
            $this->_helpers = $this->getOption('helpers');

            $this->registerHelper('dispatch', function ($controller, Request $request) {

                return $this->dispatch($request);
            });

            $this->registerHelper('dispatchError', function ($controller, $action, $format = null, array $args = null) {

                if (isset($controller->dispatchRequest) && !$format)
                    $format = $controller->dispatchRequest->getFormat();

                if (!$format)
                    throw new \Exception(
                        "Failed to dispatch error: No format given"
                    );

                return $this->dispatchError($action, $format, $args);
            });
        });

        $app->bind('afterRun', function () {

            if ($this->_loader)
                $this->_loader->unregister();

            unset($this->_loader);
            unset($this->_factory);
            unset($this->_dispatcher);

            unset($this->_instances);
        });
    }

    private function _initLoader()
    {

        $this->_loader = null;
        if ($this->getOption('createLoader')) {

            $this->_loader = new ClassLoader(
                $this->getOption('path'),
                $this->getOption('nameSpace'),
                $this->getOption('loadPattern')
            );
            $this->_loader->register();
        }
    }

    private function _initFactory()
    {

        $this->_factory = new Factory('Tale\\App\\ControllerBase');
    }

    private function _initDispatcher()
    {

        $this->_dispatcher = new Dispatcher(
            $this->_factory,
            $this->getOption('nameSpace'),
            $this->getOption('classNamePattern'),
            $this->getOption('methodNamePattern')
        );
    }

    public function setArg($key, $value)
    {

        $this->_args[$key] = $value;

        return $this;
    }

    public function registerHelper($name, $callback)
    {

        if (!is_callable($callback))
            throw new \InvalidArgumentException("Argument 2 of Controller->registerHelper needs to be valid callback");

        $this->_helpers[$name] = $callback;

        return $this;
    }

    private function _getControllerInstance($controller)
    {

        if (!in_array($controller, $this->_instances)) {

            $this->_instances[$controller] = $this->_dispatcher->createInstance($controller);

            //Now we append our args and helpers on our controller
            /**
             * @var \Tale\App\ControllerBase $internalInstance
             */
            $internalInstance = $this->_instances[$controller]->getInternalInstance();

            $internalInstance->setArgs($this->_args);
            foreach ($this->_helpers as $name => $callback)
                $internalInstance->registerHelper($name, $callback);
        }

        return $this->_instances[$controller];
    }

    public function dispatchError($action, $format, array $args = null)
    {

        return $this->dispatch(
            new Controller\Request(
                $this->getOption('errorController'),
                $action,
                $format,
                $args
            )
        );
    }

    public function dispatch(Controller\Request $request)
    {

        //This looks messier than it is
        //What happens is the following:

        //1. We create a controller (or get an existing one, getControllerInstance does that for us)
        //2. We create an empty response
        //3. We dispatch all Methods on the controller, that are called init* (initAuth, initWhatever)
        //   If one of those returns a result, we stop and that will be our final response
        //4. If no init*-Method returns anything, we call the actual action of the controller
        //5. If the dispatcher doesn't find anything, it will return a RuntimeException
        //   If we catch one, we call our ErrorController.
        //   If the ErrorController is the errornous controller, we throw the
        //   RuntimeException

        $controller = $request->getController();
        $action = $request->getAction();
        $format = $request->getFormat();
        $args = $request->getArgs();

        $response = null;
        try {

            $instance = $this->_getControllerInstance($controller);

            /**
             * @var \Tale\App\ControllerBase $controllerInstance
             */
            $controllerInstance = $instance->getInternalInstance();

            //We need our request on the controller to work with it

            $controllerInstance->setArg('dispatchRequest', $request);

            if ($controllerInstance->emit('beforeInit')) {

                //Have a look at Tale\Dispatcher\Instance to grasp the magic behind this
                //__get calls __call and __call returns a Tale\Dispatcher\CallIterator instance with all init.* methods contained
                $response = $instance->{'init.*'}->getFirstResult();
                $controllerInstance->emit('afterInit');
            }


            if (!$response) {

                if ($controllerInstance->emit('beforeAction')) {

                    $response = $instance->call($action, $args);
                    $controllerInstance->emit('afterAction');
                }
            }


        } catch (\RuntimeException $e) {

            //If the error controller wasnt found, the dispatching of it doesnt make sense
            if ($controller === $this->getOption('errorController'))
                throw $e;

            return $this->dispatchError('not-found', $format);
        }

        if (!($response instanceof Controller\Response)) {

            $response = new Controller\Response($format, $response);
        }

        return $response;
    }
}