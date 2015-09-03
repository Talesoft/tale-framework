<?php

namespace Tale\Cache\Adapter\File;

abstract class FormatBase
{

    abstract public function getExtension();
    abstract public function load($path);
    abstract public function save($path, $value);
}