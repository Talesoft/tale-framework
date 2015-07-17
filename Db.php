<?php

namespace Tale;

class Db {

    private $_config;
    private $_adapterFactory;
    private $_adapter;
    private $_cache;

    public function __construct( array $options = null ) {

        $this->_config = new Config( array_replace( [
            'nameSpace' => '',
            'lifeTime' => 3600,
            'adapter' => 'mysql',
            'options' => [
                'host' => 'localhost',
                'user' => 'root',
                'password' => '',
                'encoding' => 'utf8'
            ]
        ], $options ? $options : [] ) );

        $this->_adapterFactory = new Factory( 'Tale\\Db\\AdapterBase', [
            'mysql' => 'Tale\\Db\\Adapter\\MySql',
            'sql-lite' => 'Tale\\Db\\Adapter\\SqlLite',
            'xml' => 'Tale\\Db\\Adapter\\Xml',
            'csv' => 'Tale\\Db\\Adapter\\Csv'
        ] );

        if( isset( $this->_config->adapterAliases ) ) {

            foreach( $this->_config->adapterAliases as $alias => $className )
                $this->_adapterFactory->registerAlias( $alias, $className );
        }

        $this->_adapter = $this->_adapterFactory->createInstance( $this->_config->adapter, [
            $this->_config->options->getOptions()
        ] );
    }

    /**
     * @return Config
     */
    public function getConfig() {

        return $this->_config;
    }

    /**
     * @return Factory
     */
    public function getAdapterFactory() {

        return $this->_adapterFactory;
    }

    /**
     * @return Db\AdapterBase
     */
    public function getAdapter() {

        return $this->_adapter;
    }

    /**
     * @return Cache
     */
    public function getCache() {

        return $this->_cache;
    }

    /**
     * @param Cache $cache
     */
    public function setCache( Cache $cache ) {

        $this->_cache = $cache;
    }
}