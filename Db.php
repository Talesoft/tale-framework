<?php

namespace Tale;

class Db {

    private $_options;
    private $_adapterFactory;
    private $_adapter;

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
}