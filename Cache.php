<?php

namespace Tale;

class Cache {

    private $_config;
    private $_adapterFactory;
    private $_adapter;

    public function __construct( array $options = null ) {

        $this->_config = new Config( $options );
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

        $options = isset( $this->_config->options ) ? $this->_config->options->getOptions() : null;
        $this->_adapter = $this->_adapterFactory->createInstance( $this->_config->adapter, [ $options ] );
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

    public function load( $key, $lifeTime ) {


    }
}