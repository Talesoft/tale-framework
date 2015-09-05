<?php

namespace Tale\Data\Adapter;

class Sqlite extends MySql
{

    public function __construct(array $options = null)
    {
        parent::__construct($options);

        $this->prependOptions([
            'path' => ':memory:'
        ]);
    }

    protected function buildDsn()
    {

        $config = $this->getConfig();

        return "{$config->driver}:$config->path";
    }
}
