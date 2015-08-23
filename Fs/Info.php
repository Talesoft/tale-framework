<?php

namespace Tale\Fs;

class Info {

    private $_path;

    public function __construct( $path ) {

        $this->_path = $path;
    }

    public function getPath() {

        return $this->_path;
    }

    public function exists() {

        return file_exists( $this->_path ) || is_dir( $this->_path );
    }

    public function isDirectory() {

        return is_dir( $this->_path );
    }

    public function isFile() {

        return is_file( $this->_path );
    }

    public function isLink() {

        return is_link( $this->_path );
    }

    public function getAccessTime() {

        return new \DateTime( '@'.fileatime( $this->_path ) );
    }

    public function getModificationTime() {

        return new \DateTime( '@'.filemtime( $this->_path ) );
    }

    public function getCreationTime() {

        return new \DateTime( '@'.filectime( $this->_path ) );
    }
}