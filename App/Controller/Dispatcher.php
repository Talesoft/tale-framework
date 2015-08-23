<?php

namespace Tale\App\Controller;

use Tale\ClassLoader;
use Tale\Config;
use Tale\Factory;
use Tale\Dispatcher as TaleDispatcher;
use Tale\StringUtils;

class Dispatcher {

    private $_config;
    private $_loader;
    private $_factory;
    private $_dispatcher;
    private $_controllers;
    private $_args;
    private $_helpers;

    public function __construct( array $options = null ) {

        $this->_config = new Config( array_replace_recursive( [
            'nameSpace' => null,
            'path' => './controllers',
            'classNamePattern' => '%sController',
            'methodNamePattern' => '%sAction',
            'errorController' => 'error',
            'baseControllerClassName' => 'Tale\\App\\ControllerBase',
            'args' => [],
            'helpers' => []
        ], $options ? $options : [] ) );

        $this->_loader = new ClassLoader( $this->_config->path, $this->_config->nameSpace );
        $this->_factory = new Factory( $this->_config->baseControllerClassName );
        $this->_dispatcher = new TaleDispatcher( $this->_factory, $this->_config->nameSpace, $this->_config->classNamePattern, $this->_config->methodNamePattern );
        $this->_controllers = [];
        $this->_args = $this->_config->args->getOptions();
        $this->_helpers = $this->_config->helpers->getOptions();

        $this->_loader->register();
    }

    public function setArg( $key, $value ) {

        $this->_args[ $key ] = $value;

        return $this;
    }

    public function setArgs( array $args ) {

        foreach( $args as $key => $value )
            $this->setArg( $key, $value );

        return $this;
    }

    public function registerHelper( $name, callable $callback ) {

        $this->_helpers[ $name ] = $callback;

        return $this;
    }

    private function _getControllerInstance( $controller ) {

        if( !in_array( $controller, $this->_controllers ) ) {

            $this->_controllers[ $controller ] = $this->_dispatcher->createInstance( $controller, [
                $this->_args,
                $this->_helpers
            ] );
        }

        return $this->_controllers[ $controller ];
    }

    public function dispatchError( $action, $format, array $args = null ) {

        return $this->dispatch( new Request( $this->_config->errorController, $action, $format, $args ) );
    }

    public function dispatch( Request $request ) {

        $controller = $request->getController();
        $action = $request->getAction();
        $format = $request->getFormat();
        $args = $request->getArgs();

        $this->_args[ 'request' ] = $request;

        $result = null;
        try {

            $instance = $this->_getControllerInstance( $controller );

            //Have a look at Tale\Dispatcher\Instance to grasp the magic behind this
            //__get calls __call and __call returns a Tale\Dispatcher\CallIterator instance with all init.* methods contained
            $result = $instance->{'init.*'}->getFirstResult();

            if( !$result )
                $result = $instance->call( $action, $args );

        } catch( \RuntimeException $e ) {

            //If the error controller wasnt found, the dispatching of it doesnt make sense
            if( $controller === $this->_config->errorController )
                throw $e;

            return $this->dispatchError( 'not-found', $format );
        }

        if( !( $result instanceof Response ) ) {

            $result = new Response( $format, $result );
        }

        return $result;
    }
}