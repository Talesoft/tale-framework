<?php

namespace Tale\App;

use Tale\Config;

class Manifest extends Config {

    public function __construct( array $items = null, $flags = null ) {
        parent::__construct( $items, $flags );

        //TODO: Maybe we should put Tale\Config\Container->setDefaultOptions into Tale\Config?
        $this->mergeArray( [
            'name' => null,
            'version' => null,
            'description' => null,
            'authors' => [],

            'paths' => []
        ], true, true );
    }
}