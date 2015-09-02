<?php

namespace Tale\Debug;

use Tale\Debug\Profiler\Record;
use Tale\Dom\Html\Manipulator;
use Tale\StringUtil;

/**
 * Class Profiler
 * @package Tale\Debug
 */
class Profiler {

    /**
     * @var Record[]
     */
    private $_records;

    /**
     * @var Record
     */
    private $_startRecord;

    /**
     * @var Record
     */
    private $_lastRecord;

    /**
     *
     */
    public function __construct() {

        $this->reset();
    }

    /**
     *
     */
    public function reset() {

        $this->_records = [];
        $this->_startRecord = null;
        $this->_lastRecord = null;
    }

    /**
     * @return mixed
     */
    public function getRecords() {

        return $this->_records;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function hasRecord( $name ) {

        return isset( $this->_records[ $name ] );
    }

    /**
     * @param $name
     *
     * @return Record
     */
    public function getRecord( $name ) {

        return $this->_records[ $name ];
    }

    /**
     * @param null $name
     *
     * @return $this
     */
    public function record( $name = null ) {

        $snapshot = Snapshot::create();
        $record = new Profiler\Record( $this, $name, $snapshot, $this->_lastRecord );

        $this->_records[] = $record;
        $this->_lastRecord = $record;

        if( !$this->_startRecord )
            $this->_startRecord = $record;

        return $this;
    }

    /**
     * @return Record
     */
    public function getStartRecord() {

        return $this->_startRecord;
    }

    /**
     * @param bool|false $withOutput
     *
     * @return Manipulator
     */
    public function generateHtml( $withOutput = false ) {

        $th = [
            'Name/ID',
            'Execution Time',
            'Total Execution Time',
            'Memory',
            'Total Memory',
            'Memory Peak',
            'Avg. Memory Peak',
            'Real Memory',
            'Total Real Memory',
            'Real Memory Peak',
            'Avg. Real Memory Peak'
        ];

        $m = new Manipulator( 'div' );

        $tbl = $m->setCss( [ 'font-family' => 'monospace', 'font-size' => '8px', 'color' => '#333' ] )
                 ->table
                     ->tableCols( $th );

        foreach( $this->_records as $record ) {

            $fromStart = $record->getAbsoluteResult();
            $fromLast = $record->getResult();

            if( !$fromLast )
                $fromLast = $fromStart;

            $tr = $tbl->append( 'tr' );
            $tr->append( 'th' )
                   ->setText( $record->getName() );

            foreach( [ $fromLast->getTime(), $fromStart->getTime() ] as $time ) {

                $time = $time * 1000;

                $tr->append( 'td' )
                        ->append( 'label[title="'.$time.' ms"]' )
                            ->setText( StringUtil::timify( $time ) );
            }

            foreach( [ 
                $fromLast->getMemoryUsage(), 
                $fromStart->getMemoryUsage(),
                $fromLast->getMemoryUsagePeak(), 
                $fromStart->getMemoryUsagePeak(),
                $fromLast->getRealMemoryUsage(),
                $fromStart->getRealMemoryUsage(),
                $fromLast->getRealMemoryUsagePeak(),
                $fromStart->getRealMemoryUsagePeak()
            ] as $bytes )
                $tr->append( 'td' )
                        ->append( 'label[title="'.$bytes.' Byte"]' )
                            ->setText( StringUtil::bytify( $bytes ) );

        }

        $m->find( 'table' )->setCss( [ 'width' => '100%' ] );
        $m->find( 'td, th' )->setCss( [ 'border' => '1px solid #ccc' ] );
        $m->find( 'td' )->setCss( [ 'text-align' => 'right' ] );
        $m->find( 'tr:even, tbody th' )->setCss( [ 'background' => '#efefef' ] );

        return $m;
    }
}