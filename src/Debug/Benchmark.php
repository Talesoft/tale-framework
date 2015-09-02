<?php

namespace Tale\Debug;

/**
 * Benchmark class to benchmark single procedures properly
 * @package Tale\Debug
 */
class Benchmark {

	/**
	 * The name of this benchmark
	 *
	 * @var string
     */
	private $_name;

	/**
	 * The operation that is performed
	 *
	 * @var callable
     */
	private $_operation;

	/**
	 * The arguments that should be passed to the operation
	 *
	 * @var array
     */
	private $_args;

	/**
	 * The amount of operation calls performed
	 *
	 * @var int
     */
	private $_iterationCount;

	/**
	 * Creates a new benchmark
	 *
	 * @param string     $name		 	 The name of the benchmark
	 * @param callable   $operation		 The operation to perform
	 * @param array      $args			 The arguments that are passed to the operation
	 * @param null       $iterationCount The times the operation should be run
     */
	public function __construct( $name, callable $operation, array $args = null, $iterationCount = null ) {

		$this->_name = $name;
		$this->_operation = $operation;
		$this->_args = $args ? $args : [];
		$this->_iterationCount = $iterationCount ? $iterationCount : 1;
	}

	/**
	 * Returns the name of the benchmark
	 *
	 * @return string
     */
	public function getName() {

		return $this->_name;
	}

	/**
	 * Returns the performed operation of the benchmark
	 *
	 * @return callable
     */
	public function getOperation() {

		return $this->_operation;
	}

	/**
	 * Returns the arguments that are passed to each operation call
	 *
	 * @return array
     */
	public function getArgs() {

		return $this->_args;
	}

	/**
	 * The amount of times the operation should be called
	 *
	 * @return int
     */
	public function getIterationCount() {

        return $this->_iterationCount;
    }

	/**
	 * Performs the benchmark.
	 * At the start and at the end of the procedure call loop, it takes a snapshot
	 * of PHP background data and returns the difference of both
	 *
	 * @param bool $withOutput If set to false, output is suppressed (var_dump, echo etc.) (Default: false)
	 *
	 * @return Snapshot
     */
    public function process( $withOutput = false ) {

		$it = $this->_iterationCount;

        if( !$withOutput )
		  ob_start();

		$start = Snapshot::create();
		while( $it-- ) {

			call_user_func_array( $this->_operation, $this->_args );
		}
		$end = Snapshot::create();

        if( !$withOutput )
		  ob_get_clean();

		return $end->diff( $start );
	}
}