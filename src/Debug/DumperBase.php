<?php

namespace Tale\Debug;

abstract class DumperBase {

    private $_options;
    private $_level;
    private $_dumpedValues;

    public function __construct( array $options = null ) {

        $this->_options = array_replace( [
             'maxLevel' => 20
        ], $options ? $options : [] );
        $this->_level = null;
        $this->_dumpedValues = null;
    }

    /**
     * @return array
     */
    public function getOptions() {

        return $this->_options;
    }

    /**
     * @return null
     */
    public function getLevel() {

        return $this->_level;
    }

    public function dump( $value ) {

        $this->_level = 0;
        $this->_dumpedValues = [];

        return $this->dumpMixed( $value );
    }

    protected function dumpMixed( $value ) {

        switch( gettype( $value ) ) {
            case 'array':
            case 'object':

                if( $this->_level >= $this->_options[ 'maxLevel' ] )
                    return $this->dumpTooDeep( $value );


                if( in_array( $value, $this->_dumpedValues, true ) )
                    return $this->dumpRecursion( $value );

                $this->_dumpedValues[] = $value;

                $this->_level++;
                $str = is_array( $value ) ? $this->dumpArray( $value ) : $this->dumpObject( $value );
                $this->_level--;

                return $str;
            case 'string':

                return $this->dumpString( $value );
            case 'integer':

                return $this->dumpInteger( $value );
            case 'double':

                return $this->dumpFloat( $value );
            case 'boolean':

                return $this->dumpBoolean( $value );
            case 'resource':

                return $this->dumpResource( $value );
            case 'null':

                return $this->dumpNull( $value );
        }
    }

    abstract protected function dumpRecursion( $value );
    abstract protected function dumpTooDeep( $value );
    abstract protected function dumpArray( array $value );
    abstract protected function dumpObject( $value );
    abstract protected function dumpString( $value );
    abstract protected function dumpInteger( $value );
    abstract protected function dumpFloat( $value );
    abstract protected function dumpBoolean( $value );
    abstract protected function dumpResource( $value );
    abstract protected function dumpNull( $value );
}