<?php

namespace Tale\Debug;

use Tale\Dom\Html\Manipulator;
use Tale\Dom\Html\Manpulator,
    Tale\StringUtils;

class Profiler {

    private $_benchmarks;
    private $_records;

    public function __construct() {

        $this->reset();
    }

    public function reset() {

        $this->_benchmarks = [];
        $this->_records = [];

        return $this->record( '.start' );
    }

    public function record( $name = null ) {

        if( $name )
            $this->_records[ $name ] = Snapshot::create();
        else
            $this->_records[] = Snapshot::create();

        return $this;
    }

    //TODO: Benchmarking should be moved to a different class
    public function benchmark( $name, callable $operation, array $args = null, $iterationCount = null ) {

        $this->_benchmarks[] = new Benchmark( $name, $operation, $args, $iterationCount );

        return $this;
    }

    public function getBenchmarkResults( $withOutput = false ) {

        foreach( $this->_benchmarks as $name => $benchmark ) {

            $result = $benchmark->process( $withOutput );

            yield $benchmark->getName() => [ 'benchmark' => $benchmark, 'result' => $result ];
        }
    }

    public function getBenchmarkResultArray( $withOutput = false ) {

        return iterator_to_array( $this->getBenchmarkResults( $withOutput ) );
    }

    public function getResults() {

        $this->record( '.end' );

        reset( $this->_records );

        $start = current( $this->_records );
        $current = $start;
        foreach( $this->_records as $name => $data ) {

            $fromStart = $data->diff( $start );
            $fromLast = $data->diff( $current );

            $current = $data;

            yield $name => [ 'fromStart' => $fromStart, 'fromLast' => $fromLast ];
        }
    }

    public function getResultArray() {

        return iterator_to_array( $this->getResults() );
    }

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
                 ->headLine( 'Debug Timeline' )
                     ->parent()
                 ->table()
                     ->tableCols( $th );

        foreach( $this->getResults( $withOutput ) as $name => $data ) {

            $fromStart = $data[ 'fromStart' ];
            $fromLast = $data[ 'fromLast' ];

            $tr = $tbl->append( 'tr' );
            $tr->append( 'th' )
                   ->setText( $name );

            foreach( [ $fromLast->getTime(), $fromStart->getTime() ] as $time ) {

                $time = $time * 1000;

                $tr->append( 'td' )
                        ->append( 'label[title="'.$time.' ms"]' )
                            ->setText( StringUtils::timify( $time ) );
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
                            ->setText( StringUtils::bytify( $bytes ) );

        }

        $th = [
            'Name',
            'Iterations',
            'Execution Time',
            'Memory',
            'Memory Peak',
            'Real Memory',
            'Real Memory Peak',
        ];

        $tbl = $m->setCss( [ 'font-family' => 'monospace', 'font-size' => '8px', 'color' => '#333' ] )
                 ->headLine( 'Benchmark Results' )
                     ->parent()
                 ->table()
                     ->tableCols( $th );


        foreach( $this->getBenchmarkResults( $withOutput ) as $name => $data ) {

            $benchmark = $data[ 'benchmark' ];
            $result = $data[ 'result' ];

            $tr = $tbl->append( 'tr' );

            $tr->append( 'th' )
                    ->setText( $name )
                    ->parent()
                ->append( 'td' )
                    ->setText( $benchmark->getIterationCount() );

            $time = $result->getTime() * 1000;
            $tr->append( 'td' )
                    ->append( 'label', [ 'title' => "$time ms" ] )
                        ->setText( StringUtils::timify( $time ) );

            foreach( [ 
                $result->getMemoryUsage(),
                $result->getMemoryUsagePeak(),
                $result->getRealMemoryUsage(),
                $result->getRealMemoryUsagePeak()
            ] as $bytes )
                $tr->append( 'td' )
                        ->append( 'label', [ 'title' => "$bytes Bytes" ] )
                            ->setText( StringUtils::bytify( $bytes ) );
        }

        $m->find( 'table' )->setCss( [ 'width' => '100%' ] );
        $m->find( 'td, th' )->setCss( [ 'border' => '1px solid #ccc' ] );
        $m->find( 'td' )->setCss( [ 'text-align' => 'right' ] );
        $m->find( 'tr:even, tbody th' )->setCss( [ 'background' => '#efefef' ] );

        return $m;
    }
}