<?php

namespace Tale\Data;

use Tale\Config,
    Tale\Factory,
    Tale\Cache;

class Source {
    use Cache\OptionalTrait;

    private $_config;
    private $_adapterFactory;

    /**
     * @var AdapterBase
     */
    private $_adapter;

    private $_modelLoaders;

    //TODO: Implement caching EVERYWHERE!

    public function __construct( array $options = null ) {

        $this->_config = new Config( array_replace_recursive( [
            'nameSpace' => '',
            'databaseClassName' => 'Tale\\Data\\Database',
            'tableClassName' => 'Tale\\Data\\Table',
            'modelTableClassName' => 'Tale\\Data\\Table',
            'columnClassName' => 'Tale\\Data\\Column',
            'lifeTime' => 3600,
            'modelNameSpaces' => [],
            'adapter' => 'mysql',
            'options' => []
        ], $options ? $options : [] ) );

        $this->_adapterFactory = new Factory( 'Tale\\Data\\AdapterBase', [
            'mysql' => 'Tale\\Data\\Adapter\\MySql',
            //@TODO: The following (hehe!)
            'sql-lite' => 'Tale\\Data\\Adapter\\SqlLite',
            'mssql' => 'Tale\\Data\\Adapter\\MsSql',
            'pgsql' => 'Tale\\Data\\Adapter\\PgSql',
            'xml' => 'Tale\\Data\\Adapter\\Xml',
            'csv' => 'Tale\\Data\\Adapter\\Csv',
            'json' => 'Tale\\Data\\Adapter\\Json',
            'php' => 'Tale\\Data\\Adapter\\Php',
            'mongodb' => 'Tale\\Data\\Adapter\\MongoDb'
            //etc.
        ] );

        if( isset( $this->_config->adapterAliases ) )
            $this->_adapterFactory->registerAliases( $this->_config->adapterAliases );

        $this->_adapter = $this->_adapterFactory->createInstance( $this->_config->adapter, [
            $this->_config->options
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