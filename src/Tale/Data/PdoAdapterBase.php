<?php

namespace Tale\Data;

use PDO,
    InvalidArgumentException;

abstract class PdoAdapterBase extends AdapterBase {

    /**
     * @var PDO
     */
    private $_pdoHandle;

    public function __construct( array $options = null ) {
        parent::__construct( array_replace( [
            'driver' => null,
            'data' => [],
            'user' => null,
            'password' => null
        ], $options ? $options : [] ) );
    }

    public function open() {

        $config = $this->getConfig();

        if( !$config->driver )
            throw new InvalidArgumentException( 'Please specify a valid driver for a PDO Driver' );

        $this->_pdoHandle = new PDO( $this->buildDsn(), $config->user, $config->password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_STATEMENT_CLASS => [ __NAMESPACE__.'\\PdoStatement', [ $this ] ],
            PDO::ATTR_CASE => PDO::CASE_NATURAL
        ] );
    }

    public function close() {

        $this->_pdoHandle = null;
    }

    public function isOpen() {

        return $this->_pdoHandle ? true : false;
    }

    public function getPdoHandle() {

        return $this->_pdoHandle;
    }

    protected function buildDsn() {

        $config = $this->getConfig();

        return "{$config->driver}:".http_build_query( $config->data, '', ';' );
    }
}