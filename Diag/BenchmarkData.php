<?php

namespace Tale\Diag;

class BenchmarkData {

	private $_time;
	private $_memoryUsage;
	private $_realMemoryUsage;
	private $_memoryUsagePeak;
	private $_realMemoryUsagePeak;

	//These are somewhat fucked up because of scoping
	//TODO: Think about removing those
	private $_definedFunctions;
	private $_definedVars;
	private $_definedConstants;
	private $_definedClasses;
	private $_definedInterfaces;
	private $_definedTraits;
	private $_includedFiles;

	protected function __construct( 
		$time, 
		$memoryUsage, 
		$realMemoryUsage, 
		$memoryUsagePeak, 
		$realMemoryUsagePeak,
		array $definedFunctions,
		array $definedVars,
		array $definedConstants,
		array $definedClasses,
		array $definedInterfaces,
		array $definedTraits,
		array $includedFiles
	) {

		$this->_time = $time;
		$this->_memoryUsage = $memoryUsage;
		$this->_realMemoryUsage = $realMemoryUsage;
		$this->_memoryUsagePeak = $memoryUsagePeak;
		$this->_realMemoryUsagePeak = $realMemoryUsagePeak;
		$this->_definedFunctions = $definedFunctions;
		$this->_definedVars = $definedVars;
		$this->_definedConstants = $definedConstants;
		$this->_definedClasses = $definedClasses;
		$this->_definedInterfaces = $definedInterfaces;
		$this->_definedTraits = $definedTraits;
		$this->_includedFiles = $includedFiles;
	}

	public function getTime() {

		return $this->_time;
	}

	public function getMemoryUsage() {

		return $this->_memoryUsage;
	}

	public function getRealMemoryUsage() {

		return $this->_realMemoryUsage;
	}

	public function getMemoryUsagePeak() {

		return $this->_memoryUsagePeak;
	}

	public function getRealMemoryUsagePeak() {
		
		return $this->_realMemoryUsagePeak;
	}

	public function getDefinedFunctions() {

		return $this->_definedFunctions;
	}

	public function getDefinedVars() {

		return $this->_definedVars;
	}

	public function getDefinedConstants() {

		return $this->_definedConstants;
	}

	public function getDefinedClasses() {

		return $this->_definedClasses;
	}

	public function getDefinedInterfaces() {

		return $this->_definedInterfaces;
	}

	public function getDefinedTraits() {

		return $this->_definedTraits;
	}

	public function getIncludedFiles() {

		return $this->_includedFiles;
	}

	public function diff( BenchmarkData $other ) {

        $funcs = $other->getDefinedFunctions();
		return new self(
			$this->_time - $other->getTime(),
			$this->_memoryUsage - $other->getMemoryUsage(),
			$this->_realMemoryUsage - $other->getRealMemoryUsage(),
			( $this->_memoryUsagePeak + $other->getMemoryUsagePeak() ) / 2,
			( $this->_realMemoryUsagePeak + $other->getRealMemoryUsagePeak() ) / 2,
			[ 
                'internal' => array_diff( 
                    $this->_definedFunctions[ 'internal' ],
                    $funcs[ 'internal' ]
                ),
                'user' => array_diff(
                    $this->_definedFunctions[ 'user' ],
                    $funcs[ 'user' ]
                )
            ],
			array_diff_assoc( $this->_definedVars, $other->getDefinedVars() ),
			array_diff_assoc( $this->_definedConstants, $other->getDefinedConstants() ),
			array_diff( $this->_definedClasses, $other->getDefinedClasses() ),
			array_diff( $this->_definedInterfaces, $other->getDefinedInterfaces() ),
			array_diff( $this->_definedTraits, $other->getDefinedTraits() ),
			array_diff( $this->_includedFiles, $other->getIncludedFiles() )
		);
	}

	public static function collect() {

		return new self(
			microtime( true ),
			memory_get_usage( false ),
			memory_get_usage( true ),
			memory_get_peak_usage( false ),
			memory_get_peak_usage( true ),
			get_defined_functions(),
			get_defined_vars(),
			get_defined_constants(),
			get_declared_classes(),
			get_declared_interfaces(),
			get_declared_traits(),
			get_included_files()
		);
	}
}