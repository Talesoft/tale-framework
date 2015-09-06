<?php

namespace Tale\Data;

use Tale\StringUtil;

/**
 * Class Database
 * @package Tale\Data
 */
class Database extends NamedEntityBase
{

	/**
	 * @var \Tale\Data\Source
     */
	private $_source;

	private $_tables;

	/**
	 * @param \Tale\Data\Source $source
	 * @param                   $name
     */
	public function __construct(Source $source, $name)
	{
		parent::__construct($name);

		$this->_source = $source;
		$this->_tables = [];
	}

	/**
	 * @return \Tale\Data\Source
     */
	public function getSource()
	{

		return $this->_source;
	}

	/**
	 * @return mixed
     */
	public function exists()
	{

		return $this->getSource()->hasDatabase($this);
	}

	/**
	 * @return $this
     */
	public function load()
	{

		$this->getSource()->loadDatabase($this);

		return parent::load();
	}

	/**
	 * @return $this
     */
	public function save()
	{

		$this->getSource()->saveDatabase($this);

		return parent::save();
	}

	/**
	 * @param array|null $data
	 *
	 * @return $this
     */
    public function create(array $data = null)
	{

		$this->getSource()->createDatabase($this);

		return parent::create();
	}

	/**
	 * @return $this
     */
	public function remove()
	{

		$this->getSource()->removeDatabase($this);

		return parent::remove();
	}

	/**
	 * @return \Generator
     */
    public function getTables()
	{

        $source = $this->getSource();
        $tableNames = $source->fetchCached(
            "databases.$this.table-names",
            function() use($source) {

            return iterator_to_array($source->getTableNames($this));
        }, $source->getOption('lifeTime'));

		foreach ($tableNames as $name)
            if (!isset($this->_tables[$name]))
                $this->_tables[$name] = new Table($this, $name);

        return new Entity\Collection($this->_tables);
	}

	/**
	 * @param            $name
     *
	 * @return \Tale\Data\Table
     */
    public function getTable($name)
	{

		if (!isset($this->_tables[$name]))
            $this->_tables[$name] = new Table($this, $name);

        return $this->_tables[$name];
	}

	/**
	 * @param $name
	 *
	 * @return \Tale\Data\Table
     */
    public function __get($name)
	{

		return $this->getTable($this->getSource()->inflectTableName($name));
	}
}