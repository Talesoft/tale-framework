<?php

namespace Tale;

use InvalidArgumentException;

/*

WARNING!

THIS class should have NO requirements at all since it keeps the include-loading really small (just one include)
Don't change it, rather extend it or create a new implementation in another namespace

*/
class ClassLoader {

    const DEFAULT_PATTERN = '%s.php';

    private $_path;
    private $_nameSpace;
    private $_pattern;
    private $_handle;
    private $_registered;

    public function __construct( $path = null, $nameSpace = null, $pattern = null ) {

        $this->_path = $path;
        $this->_nameSpace = $nameSpace;
        $this->_pattern = $pattern ? $pattern : self::DEFAULT_PATTERN;
        $this->_handle = [ $this, 'load' ];
    }

    public function __destruct() {

        if( $this->_registered )
            $this->unregister();
    }

    public function setNameSpace( $nameSpace ) {

        $this->_nameSpace = $nameSpace;
    }

    public function register() {

        spl_autoload_register( $this->_handle );
        $this->_registered = true;

        return $this;
    }

    public function unregister() {

        spl_autoload_unregister( $this->_handle );
        $this->_registered = false;

        return $this;
    }

    public function isRegistered() {

        return $this->_registered;
    }

    public function load( $className ) {

        $name = $className;
        if( $this->_nameSpace ) {

            $ns = rtrim( $this->_nameSpace, '\\' ).'\\';

            $nameLen = strlen( $className );
            $nsLen = strlen( $ns );

            if( $nameLen < $nsLen || substr( $className, 0, $nsLen ) !== $ns )
                return false;

            $name = substr( $name, $nsLen );
        }

        $ds = \DIRECTORY_SEPARATOR;
        $path = $this->_path ? $this->_path.$ds : '';
        $path .= str_replace( [ '_', '\\' ], $ds, sprintf( $this->_pattern, $name ) );

        if( ( $path = stream_resolve_include_path( $path ) ) !== false ) {

            include $path;
        }

        return class_exists( $className, false ); 
    }
}