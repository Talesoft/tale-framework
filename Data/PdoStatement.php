<?php

namespace Tale\Data;

class PdoStatement extends \PDOStatement {

	private $_pdoAdapter;

	private function __construct( PdoAdapterBase $pdoAdapter ) {

		$this->_pdoAdapter = $pdoAdapter;
	}

	public function getPdoAdapter() {

		return $this->_pdoAdapter;
	}
}