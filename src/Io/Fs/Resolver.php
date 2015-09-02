<?php

namespace Tale\Io\Fs;

class Resolver {

    private $_paths;
    private $_allowedExtensions;

    public function __construct( array $paths = null, array $allowedExtensions = null ) {

        $this->_paths = $paths ? $paths : explode( \PATH_SEPARATOR, get_include_path() );
        $this->_allowedExtensions = $allowedExtensions;
    }

    public function resolve( $path ) {

        foreach( $this->_paths as $searchPath ) {

            $fullPath = "$searchPath/$path";

            //First lets check without extension
            if( file_exists( $fullPath ) )
                return $fullPath;

            if( $this->_allowedExtensions ) {

                foreach( $this->_allowedExtensions as $ext ) {

                    $fullPath .= ".$ext";

                    if( file_exists( $fullPath ) )
                        return $fullPath;
                }
            }
        }

        return null;
    }
}