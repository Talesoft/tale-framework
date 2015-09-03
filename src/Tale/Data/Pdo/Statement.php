<?php

namespace Tale\Data\Pdo;

use PDOStatement;

class Statement extends PDOStatement
{

	private $_adapter;

	private function __construct(AdapterBase $adapter)
	{

		$this->_adapter = $adapter;
	}

	public function getAdapter()
	{

		return $this->_adapter;
	}
}