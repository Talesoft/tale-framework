<?php

namespace Tale\Data;

use Tale\ClassLoader;
use Tale\Config,
    Tale\Factory,
    Tale\Cache;
use Tale\Util\StringUtil;

class Source
{
    use Config\OptionalTrait;
    use Cache\OptionalTrait;

    private $_adapterFactory;

    /**
     * @var AdapterBase
     */
    private $_adapter;

    private $_modelNameSpaces;

    //TODO: Implement caching EVERYWHERE!

    public function __construct(array $options = null)
    {

        $this->appendOptions([
            'nameSpace'           => '',
            'databaseClassName'   => 'Tale\\Data\\Database',
            'tableClassName'      => 'Tale\\Data\\Table',
            'modelTableClassName' => 'Tale\\Data\\Table',
            'columnClassName'     => 'Tale\\Data\\Column',
            'lifeTime'            => 3600,
            'modelNameSpaces'     => [],
            'adapterAliases'      => [],
            'adapter'             => 'mysql',
            'options'             => []
        ]);

        if($options)
            $this->appendOptions($options);

        $this->_adapterFactory = new Factory('Tale\\Data\\AdapterBase', [
            'mysql'    => 'Tale\\Data\\Adapter\\MySql',
            'sqlite' => 'Tale\\Data\\Adapter\\Sqlite',
            //@TODO: The following (hehe!)
            'mssql'    => 'Tale\\Data\\Adapter\\MsSql',
            'pgsql'    => 'Tale\\Data\\Adapter\\PgSql',
            'xml'      => 'Tale\\Data\\Adapter\\Xml',
            'csv'      => 'Tale\\Data\\Adapter\\Csv',
            'json'     => 'Tale\\Data\\Adapter\\Json',
            'php'      => 'Tale\\Data\\Adapter\\Php',
            'mongodb'  => 'Tale\\Data\\Adapter\\MongoDb'
            //etc.
        ]);

        $this->_adapterFactory->registerAliases($this->getOption('adapterAliases'));

        $this->_adapter = null;
        $this->_modelNameSpaces = [];

        //TODO: Maybe the model-feature should be de-coupled from the Source
        foreach ($this->getOption('modelNameSpaces') as $nameSpace => $path)
            $this->registerModelNameSpace($nameSpace, $path);
    }

    public function __destruct()
    {

        foreach ($this->_modelNameSpaces as $nameSpace => $loader)
            if ($loader && $loader->isRegistered())
                $loader->unregister();
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
        if(!$this->_adapter) {

            $this->_adapter = $this->_adapterFactory->createInstance($this->getOption('adapter'), [
                $this->getOption('options')
            ]);
        }

        return $this->_adapter;
    }

    public function registerModelNameSpace($nameSpace, $path = null)
    {

        $loader = null;
        if ($path) {

            $loader = new ClassLoader($nameSpace, $path);
            $loader->register();
        }

        $this->_modelNameSpaces[$nameSpace] = $loader;

        return $this;
    }

    public function getModelClassName($tableName)
    {

        foreach ($this->_modelNameSpaces as $nameSpace => $path) {

            $className = ltrim($nameSpace, '\\').'\\'.StringUtil::camelize(StringUtil::singularize($tableName));

            if (class_exists($className))
                return $className;
        }

        return null;
    }

    public function getDatabases($load = false)
    {

        foreach ($this->getDatabaseNames() as $name)
            yield $name => $this->getDatabase($name, $load);
    }

    public function getDatabaseArray($load = false)
    {

        return iterator_to_array($this->getDatabases($load));
    }

    public function getDatabase($name, $load = false)
    {

        $className = $this->_config->databaseClassName;
        return new $className($this, $name, $load);
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