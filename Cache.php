<?php

namespace Tale;

class Cache {

    private $_config;
    private $_adapterFactory;
    private $_adapter;

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

        if( isset( $this->_config->adapterAliases ) ) {

            foreach( $this->_config->adapterAliases as $alias => $className )
                $this->_adapterFactory->registerAlias( $alias, $className );
        }

        $this->_adapter = $this->_adapterFactory->createInstance( $this->_config->adapter, [
            $this->_config->options->getOptions()
        ] );
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

    public function getSubCache( $nameSpace, array $options = null ) {

        $subNs = isset( $this->_config->nameSpace )
               ? $this->_config->nameSpace.'.'
               : '';

        $options[ 'nameSpace' ] = "$subNs$nameSpace";
        return new self( $this->_config->merge( $options )->getOptions() );
    }

    public function load( $key, callable $action, $lifeTime = null ) {

        $lifeTime = !is_null( $lifeTime ) ? $lifeTime : $this->_config->lifeTime;

        if( $this->_adapter->exists( $key ) ) {

            return $this->_adapter->get( $key );
        }

        $result = call_user_func( $action, $key );
        $this->_adapter->set( $key, $result, $lifeTime );

        return $result;
    }
}