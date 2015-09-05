<?php

namespace Tale\Theme;

use Tale\Config;

abstract class ConverterBase
{
    use Config\OptionalTrait;

    private $_manager;

    public function __construct(Manager $manager, array $options = null)
    {

        $this->_manager = $manager;

        $this->appendOptions($options);
    }

    public function getManager()
    {

        return $this->_manager;
    }

    abstract public function convert($inputPath);
}