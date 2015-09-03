<?php

namespace Tale\Cache\Adapter\File\Format;

use Tale\Cache\Adapter\File\FormatBase;

class Serialize extends FormatBase
{

    public function getExtension()
    {

        return '.tmp';
    }

    public function load($path)
    {

        return unserialize(file_get_contents($path));
    }

    public function save($path, $value)
    {

        file_put_contents($path, serialize($value));

        return $this;
    }
}