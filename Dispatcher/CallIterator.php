<?php

namespace Tale\Dispatcher;

class CallIterator implements \IteratorAggregate {

    private $_instance;
    private $_methods;
    private $_args;

    public function __construct( $instance, array $methods, array $args = null ) {

        $this->_instance = $instance;
        $this->_methods = $methods;
        $this->_args = $args ? $args : [];
    }

    /**
     * @return mixed
     */
    public function getInstance() {

        return $this->_instance;
    }

    /**
     * @return array
     */
    public function getMethods() {

        return $this->_methods;
    }

    /**
     * @return array
     */
    public function getArgs() {

        return $this->_args;
    }

    public function getIterator() {

        foreach( $this->_methods as $method )
            yield $method => call_user_func_array( [ $this->_instance, $method ], $this->_args );
    }

    public function getFirstResult() {

        foreach( $this as $result )
            if( $result )
                return $result;

        return null;
    }

    public function getAllResults() {

        return iterator_to_array( $this );
    }
}