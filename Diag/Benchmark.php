<?php

namespace Tale\Diag;

use Exception;

class Benchmark {

	private $_name;
	private $_operation;
	private $_args;
	private $_iterationCount;

	public function __construct( $name, callable $operation, array $args = null, $iterationCount = null ) {

		$this->_name = $name;
		$this->_operation = $operation;
		$this->_args = $args ? $args : [];
		$this->_iterationCount = $iterationCount ? $iterationCount : 1;
	}

	public function getName() {

		return $this->_name;
	}

	public function getOperation() {

		return $this->_operation;
	}

	public function getArgs() {

		return $this->_args;
	}

    public function getIterationCount() {

        return $this->_iterationCount;
    }

	public function process( $withOutput = false ) {

		$it = $this->_iterationCount;

        if( !$withOutput )
		  ob_start();

		$start = BenchmarkData::create();
		while( $it-- ) {

			call_user_func_array( $this->_operation, $this->_args );
		}
		$end = BenchmarkData::create();

        if( !$withOutput )
		  ob_get_clean();

		return $end->diff( $start );
	}
}