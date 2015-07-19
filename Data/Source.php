<?php

namespace Tale\Data;

use Tale\Config,
    Tale\Factory,
    Tale\Cache;

class Source {

    private $_config;
    private $_adapterFactory;

    /**
     * @var AdapterBase
     */
    private $_adapter;

    /**
     * @var Cache
     */
    private $_cache;

    private $_modelLoaders;

    //TODO: Implement caching EVERYWHERE!

    public function __construct( array $options = null ) {

        $this->_config = new Config( array_replace_recursive( [
            'nameSpace' => '',
            'databaseClassName' => 'Tale\\Data\\Database',
            'tableClassName' => 'Tale\\Data\\Table',
            'modelTableClassName' => 'Tale\\Model\\Table',
            'columnClassName' => 'Tale\\Data\\Column',
            'lifeTime' => 3600,
            'modelNameSpaces' => [],
            'adapter' => 'mysql',
            'options' => []
        ], $options ? $options : [] ) );

        $this->_adapterFactory = new Factory( 'Tale\\Data\\AdapterBase', [
            'mysql' => 'Tale\\Data\\Adapter\\MySql',
            'sql-lite' => 'Tale\\Data\\Adapter\\SqlLite',
            'xml' => 'Tale\\Data\\Adapter\\Xml',
            'csv' => 'Tale\\Data\\Adapter\\Csv'
        ] );

        if( isset( $this->_config->adapterAliases ) )
            $this->_adapterFactory->registerAliases( $this->_config->adapterAliases );

        $this->_adapter = $this->_adapterFactory->createInstance( $this->_config->adapter, [
            $this->_config->options->getOptions()
        ] );

        $this->_cache = null;

        if( isset( $this->_config->modelNameSpaces ) ) {

            foreach( $this->_config->modelNameSpaces as $nameSpace => $path ) {

                $loader = new \Tale\ClassLoader( $path, $nameSpace );
                $this->_modelLoaders = $loader;
                $loader->register();
            }
        }

        $this->open();
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
     * @return AdapterBase
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
     *
     * @return $this
     */
    public function setCache( Cache $cache ) {

        $this->_cache = $cache;
        $cache->bind( $this );

        return $this;
    }

    public function getDatabases( $load = false ) {

        foreach( $this->getDatabaseNames() as $name )
            yield $name => $this->getDatabase( $name, $load );
    }

    public function getDatabaseArray( $load = false ) {

        return iterator_to_array( $this->getDatabases( $load ) );
    }

    public function getDatabase( $name, $load = false ) {

        $className = $this->_config->databaseClassName;
        return new $className( $this, $name, $load );
    }

    public function __get( $name ) {

        return $this->getDatabase( $this->inflectDatabaseName( $name ) );
    }

    public function __call( $method, array $args = null ) {

        return call_user_func_array( [ $this->_adapter, $method ], $args );
    }
}