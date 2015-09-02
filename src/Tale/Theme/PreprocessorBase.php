<?php

namespace Tale\Theme;

use Tale\Config;

abstract class PreprocessorBase {

    private $_manager;
    private $_config;

    public function __construct( Manager $manager, array $options = null ) {

        $this->_manager = $manager;
        $this->_config = new Config( $options ? $options : [] );
    }

    public function getManager() {

        return $this->_manager;
    }

    public function getConfig() {

        return $this->_config;
    }

    abstract public function process( $inputPath, $outputPath );
}