<?php

namespace Tale\App;

use Tale\Config;

class Manifest extends Config
{

    public function __construct(array $items = null, $flags = null)
    {
        parent::__construct($items, $flags);

        $this->mergeArray([
            'name'        => null,
            'version'     => null,
            'description' => null,
            'authors'     => []
        ], true, true);
    }
}