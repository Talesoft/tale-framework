<?php

namespace Tale;

use InvalidArgumentException,
    Closure;

class Cache {

    private $_config;
    private $_adapterFactory;
    private $_adapter;
    private $_boundObject;

    public function __construct( array $options = null ) {

        $this->_config = new Config( array_replace( [
            'nameSpace' => '',
            'lifeTime' => 3600,
            'adapter' => 'file',
            'options' => [
                'path' => './cache'
            ]
        ], $options ? $options : [] ) );

        $this->_adapterFactory = new Factory( 'Tale\\Cache\\AdapterBase', [
            'file' => 'Tale\\Cache\\Adapter\\File',
            'apc' => 'Tale\\Cache\\Adapter\\Apc',
            'memcached' => 'Tale\\Cache\\Adapter\\Memcached',
            'xcache' => 'Tale\\Cache\\Adapter\\Xcache'
        ] );

        if( isset( $this->_config->adapterAliases ) )
            $this->_adapterFactory->registerAliases( $this->_config->adapterAliases );

        $this->_adapter = $this->_adapterFactory->createInstance( $this->_config->adapter, [
            $this->_config->options
        ] );

        $this->_boundObject = null;
    }

    public function getConfig() {

        return $this->_config;
    }

    public function getAdapterFactory() {

        return $this->_adapterFactory;
    }

    public function getAdapter() {

        return $this->_adapter;
    }

    public function getBoundObject() {

        return $this->_boundObject;
    }

    public function bind( $boundObject ) {

        if( !is_object( $boundObject ) )
            throw new InvalidArgumentException( "Invalid argument 1 passed to bind, object expected" );

        $this->_boundObject = $boundObject;

        return $this;
    }

    public function unbind() {

        $this->_boundObject = null;

        return $this;
    }

    public function createSubCache( $nameSpace, array $options = null ) {

        $subNs = !empty( $this->_config->nameSpace )
               ? $this->_config->nameSpace.'.'
               : '';

        $config = clone $this->_config;
        $config->nameSpace = "$subNs$nameSpace";
        return new self( $config->getItems() );
    }

    public function load( $key, callable $action, $lifeTime = null ) {

        $lifeTime = !is_null( $lifeTime ) ? $lifeTime : $this->_config->lifeTime;

        $key = !empty( $this->_config->nameSpace )
             ? $this->_config->nameSpace.".$key"
             : $key;

        if( $this->_boundObject && $action instanceof Closure )
            $action = $action->bindTo( $this->_boundObject, $this->_boundObject );

        if( $this->_adapter->exists( $key ) ) {

            return $this->_adapter->get( $key );
        }

        //TODO: Maybe we need $args here?
        $result = call_user_func( $action );
        $this->_adapter->set( $key, $result, $lifeTime );

        return $result;
    }
}