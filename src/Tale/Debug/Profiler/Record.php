<?php

namespace Tale\Debug\Profiler;

use Tale\Debug\Profiler,
    Tale\Debug\Snapshot;

/**
 * Class Record
 *
 * @package Tale\Debug\Profiler
 */
class Record {

    /**
     * @var Profiler
     */
    private $_profiler;

    /**
     * @var string
     */
    private $_name;

    /**
     * @var Snapshot
     */
    private $_snapshot;

    /**
     * @var Record
     */
    private $_previousRecord;

    /**
     * @param Profiler    $profiler
     * @param string      $name
     * @param Snapshot    $snapshot
     * @param Record|null $previousRecord
     */
    public function __construct( Profiler $profiler, $name, Snapshot $snapshot, Record $previousRecord = null ) {

        $this->_profiler = $profiler;
        $this->_name = $name;
        $this->_snapshot = $snapshot;
        $this->_previousRecord = $previousRecord;
    }

    /**
     * @return Profiler
     */
    public function getProfiler() {

        return $this->_profiler;
    }

    /**
     * @return string
     */
    public function getName() {

        return $this->_name;
    }

    /**
     * @return Snapshot
     */
    public function getSnapshot() {

        return $this->_snapshot;
    }

    /**
     * @return Record
     */
    public function getPreviousRecord() {

        return $this->_previousRecord;
    }

    /**
     * @return Snapshot|null
     */
    public function getResult() {

        if( !$this->_previousRecord )
            return null;

        return $this->_snapshot->diff( $this->_previousRecord->getSnapshot() );
    }

    /**
     * @return Snapshot
     */
    public function getAbsoluteResult() {

        return $this->_snapshot->diff( $this->_profiler->getStartRecord()->getSnapshot() );
    }
}