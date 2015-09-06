<?php

namespace Tale\Data;

use Tale\Config,
    Tale\Factory,
    Tale\Cache;

//All methods on the source will be routed to the driver
//This allows for our lazy loading mechanism
/**
 * @method string inflectDatabaseName( $string )
 * @method string inflectTableName( $string )
 * @method string inflectColumnName( $string )
 * @method string inflectInputColumnName( $string )
 * @method string inflectOutputColumnName( $string )
 * @method \Tale\Data\AdapterBase open()
 * @method \Tale\Data\AdapterBase close()
 * @method bool isOpen()
 * @method string encode( $value )
 * @method string decode( $value )
 * @method string[] getDatabaseNames()
 * @method bool hasDatabase( Database $database )
 * @method \Tale\Data\AdapterBase loadDatabase( Database $database )
 * @method \Tale\Data\AdapterBase saveDatabase( Database $database )
 * @method \Tale\Data\AdapterBase createDatabase( Database $database )
 * @method \Tale\Data\AdapterBase removeDatabase( Database $database )
 * @method string[] getTableNames( Database $database )
 * @method bool hasTable( Table $table )
 * @method \Tale\Data\AdapterBase loadTable( Table $table )
 * @method \Tale\Data\AdapterBase saveTable( Table $table )
 * @method \Tale\Data\AdapterBase createTable( Table $table, array $columns )
 * @method \Tale\Data\AdapterBase removeTable( Table $table )
 * @method string[] getColumnNames( Table $table )
 * @method bool hasColumn( Column $column )
 * @method \Tale\Data\AdapterBase loadColumn( Column $column )
 * @method \Tale\Data\AdapterBase saveColumn( Column $column )
 * @method \Tale\Data\AdapterBase createColumn( Column $column )
 * @method \Tale\Data\AdapterBase removeColumn( Column $column )
 * @method int countRows( Query $query, $field = null, $distinct = false )
 * @method \Generator loadRows( Query $query, array $fields = null, $as = null )
 * @method \Tale\Data\AdapterBase saveRows( Query $query, array $data )
 * @method \Tale\Data\AdapterBase createRow( Table $table, array $data )
 * @method \Tale\Data\AdapterBase removeRows( Query $query )
 * @method mixed getLastId()
 */
class Source
{

    use Config\OptionalTrait;
    use Cache\OptionalTrait;

    private $_adapterFactory;

    /**
     * @var AdapterBase
     */
    private $_adapter;

    private $_databases;

    //TODO: Implement caching EVERYWHERE!

    public function __construct(array $options = null)
    {

        $this->appendOptions(
            [
                'nameSpace'         => '',
                'lifeTime'          => 3600,
                'adapterAliases'    => [],
                'adapter'           => 'mysql',
                'options'           => []
            ]
        );

        if ($options)
            $this->appendOptions($options);

        $this->_adapterFactory = new Factory(
            'Tale\\Data\\AdapterBase', [
                'mysql'   => 'Tale\\Data\\Adapter\\MySql',
                'sqlite'  => 'Tale\\Data\\Adapter\\Sqlite',
                //@TODO: The following (hehe!)
                'mssql'   => 'Tale\\Data\\Adapter\\MsSql',
                'pgsql'   => 'Tale\\Data\\Adapter\\PgSql',
                'xml'     => 'Tale\\Data\\Adapter\\Xml',
                'csv'     => 'Tale\\Data\\Adapter\\Csv',
                'json'    => 'Tale\\Data\\Adapter\\Json',
                'php'     => 'Tale\\Data\\Adapter\\Php',
                'mongodb' => 'Tale\\Data\\Adapter\\MongoDb'
                //etc.
            ]
        );

        $this->_adapterFactory->registerAliases($this->getOption('adapterAliases'));
        $this->_adapter = null;
        $this->_databases = [];
    }

    public function __destruct()
    {

        if ($this->_adapter)
            $this->_adapter->__destruct();
    }

    /**
     * @return Factory
     */
    public function getAdapterFactory()
    {

        return $this->_adapterFactory;
    }

    /**
     * @return AdapterBase
     */
    public function getAdapter()
    {

        //We get the adapter in a lazy way
        //If we don't use the DB, we don't connect
        if (!$this->_adapter) {

            $this->_adapter = $this->_adapterFactory->createInstance(
                $this->getOption('adapter'), [
                    $this->getOption('options')
                ]
            );
        }

        return $this->_adapter;
    }


    public function getDatabases()
    {

        $databaseNames = $this->fetchCached(
            "database-names", function () {

            return iterator_to_array($this->getDatabaseNames());
        }, $this->getOption('lifeTime'));

        foreach ($databaseNames as $name)
            if (!isset($this->_databases[$name]))
                $this->_databases = new Database($this, $name);

        return new Entity\Collection($this->_databases);
    }

    public function getDatabase($name)
    {

        if (!isset($this->_databases[$name])) {

            $this->_databases[$name] = new Database($this, $name);
        }

        return $this->_databases[$name];
    }

    public function __get($name)
    {

        return $this->getDatabase($this->inflectDatabaseName($name));
    }

    public function __call($method, array $args = null)
    {

        return call_user_func_array([$this->getAdapter(), $method], $args);
    }
}