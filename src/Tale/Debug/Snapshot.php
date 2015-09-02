<?php

namespace Tale\Debug;

/**
 * Respresents a snapshot containing information about execution-time, memory usage etc.
 * at the time of taking this snapshot
 *
 * Use Snapshot::create() to create a snapshot of current PHP environment values
 *
 * @package Tale\Debug
 */
class Snapshot {

    /**
     * Respresents the time in micro-seconds
     *
     * @var float
     */
    private $_time;

    /**
     * The memory usage in bytes
     *
     * @var int
     */
    private $_memoryUsage;

    /**
     * The actual memory reserved in bytes (e.g. pre-allocated chunks)
     *
     * @var int
     */
    private $_realMemoryUsage;

    /**
     * The peak memory usage in bytes
     *
     * @var int
     */
    private $_memoryUsagePeak;

    /**
     * The actual peak memory reached in bytes (e.g. pre-allocated chunks)
     *
     * @var int
     */
    private $_realMemoryUsagePeak;

    /**
     * Creates a new Snapshot object with user-defined values
     *
     * @param float $time The time in micro-seconds (e.g. microtime( true ))
     * @param int $memoryUsage The memory usage in bytes (e.g. memory_get_usage())
     * @param int $realMemoryUsage The real memory usage in bytes (e.g. memory_get_usage( true ))
     * @param int $memoryUsagePeak The memory usage peak (e.g. memory_get_peak_usage())
     * @param int $realMemoryUsagePeak The real memory usage peak (e.g. memory_get_peak_usage( true ))
     */
    protected function __construct( $time, $memoryUsage, $realMemoryUsage, $memoryUsagePeak, $realMemoryUsagePeak ) {

		$this->_time = $time;
		$this->_memoryUsage = $memoryUsage;
		$this->_realMemoryUsage = $realMemoryUsage;
		$this->_memoryUsagePeak = $memoryUsagePeak;
		$this->_realMemoryUsagePeak = $realMemoryUsagePeak;
	}

    /**
     * Returns the saved time in micro-seconds.
     * This is the time in microseconds at the time of taking this snapshot
     * or the total execution time between two diff-ed snapshots
     *
     * @return float The time in micro-seconds
     */
    public function getTime() {

		return $this->_time;
	}

    /**
     * Returns the memory usage in bytes
     * This is overall memory usage at the time of taking this snapshot
     * or the total memory used between two diff-ed snapshots
     *
     * Memory usage means the actual memory used by the variables and constructs
     * used in the current script execution
     *
     * @return int
     */
    public function getMemoryUsage() {

		return $this->_memoryUsage;
	}

    /**
     * Returns the real memory usage in bytes
     * This is overall memory usage at the time of taking this snapshot
     * or the total memory used between two diff-ed snapshots
     *
     * Real memory means, that pre-allocated chunks of memory are taken into account
     *
     * @return int
     */
    public function getRealMemoryUsage() {

		return $this->_realMemoryUsage;
	}

    /**
     * Returns the peak of memory usage in bytes
     * This is overall peak memory usage at the time of taking this snapshot
     * or the average peak between this and a diff-ed snapshot
     *
     * Memory usage means the actual memory used by the variables and constructs
     * used in the current script execution
     *
     * @return int
     */
    public function getMemoryUsagePeak() {

		return $this->_memoryUsagePeak;
	}

    /**
     * Returns the real peak of memory usage in bytes
     * This is overall real peak memory usage at the time of taking this snapshot
     * or the average peak between this and a diff-ed snapshot
     *
     * Real memory means, that pre-allocated chunks of memory are taken into account
     *
     * @return int
     */
    public function getRealMemoryUsagePeak() {
		
		return $this->_realMemoryUsagePeak;
	}

    /**
     * Subtracts a snapshot from another one.
     *
     * The result will be a snapshot describing the execution metrics between the two snapshots
     * e.g. execution-time between two snapshots, memory-consumption from one snapshot to another etc.
     *
     * @param Snapshot $other
     *
     * @return Snapshot
     */
    public function diff( Snapshot $other ) {

		return new self(
			$this->_time - $other->getTime(),
			$this->_memoryUsage - $other->getMemoryUsage(),
			$this->_realMemoryUsage - $other->getRealMemoryUsage(),
			( $this->_memoryUsagePeak + $other->getMemoryUsagePeak() ) / 2,
			( $this->_realMemoryUsagePeak + $other->getRealMemoryUsagePeak() ) / 2
		);
	}

    /**
     * Creates a new snapshot based on current PHP environment metrics
     *
     * Basically, this fills a new instance of a snapshot with:
     * microtime( true )
     * memory_get_usage( false )
     * memory_get_usage( true )
     * memory_get_peak_usage( false )
     * and
     * memory_get_peak_usage( true )
     *
     * @return Snapshot
     */
    public static function create() {

		return new self(
			microtime( true ),
			memory_get_usage( false ),
			memory_get_usage( true ),
			memory_get_peak_usage( false ),
			memory_get_peak_usage( true )
		);
	}
}