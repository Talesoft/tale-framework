<?php

namespace Tale\Test;

use Tale\Factory;
use Tale\Dispatcher;

class Suite {

    private $_factory;
    private $_dispatcher;

    public function __construct( $nameSpace = null ) {

        $this->_factory = new Factory( __NAMESPACE__.'\\CaseBase' );
        $this->_dispatcher = new Dispatcher( $this->_factory, $nameSpace, '%sCase', 'test%s' );
    }

    public function test( $className ) {

        $target = $this->_dispatcher->createTarget( $className );
        $instance = $target->getInstance();

        $target->{'test(.*)'}();
    }
}