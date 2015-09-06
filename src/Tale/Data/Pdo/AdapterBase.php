<?php

namespace Tale\Data\Pdo;

use PDO;
use InvalidArgumentException;
use Tale\Data\AdapterBase as DataAdapterBase;

abstract class AdapterBase extends DataAdapterBase
{

    /**
     * @var PDO
     */
    private $_handle;

    public function __construct(array $options = null)
    {
        parent::__construct($options);

        $this->prependOptions([
            'driver'   => null,
            'data'     => [],
            'user'     => null,
            'password' => null
        ]);
    }

    public function open()
    {

        if (!$this->getOption('driver'))
            throw new InvalidArgumentException('Please specify a valid driver for a PDO Driver');

        $this->_handle = new PDO($this->buildDsn(), $this->getOption('user'), $this->getOption('password'), [
            PDO::ATTR_ERRMODE         => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_STATEMENT_CLASS => [__NAMESPACE__.'\\Statement', [$this]],
            PDO::ATTR_CASE            => PDO::CASE_NATURAL
        ]);

        return $this;
    }

    public function close()
    {

        $this->_handle = null;

        return $this;
    }

    public function isOpen()
    {

        return $this->_handle ? true : false;
    }

    public function getHandle()
    {

        if (!$this->isOpen())
            $this->open();

        return $this->_handle;
    }

    protected function buildDsn()
    {

        $config = $this->getConfig();

        return "{$config->driver}:".http_build_query($config->data, '', ';');
    }
}