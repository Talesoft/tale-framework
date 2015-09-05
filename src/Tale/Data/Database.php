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

	/**
	 * @param \Tale\Data\Source $source
	 * @param                   $name
	 * @param bool|false        $load
     */
	public function __construct(Source $source, $name, $load = false)
	{
		parent::__construct($name);

		$this->_source = $source;

		if ($load)
			$this->load();
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

		return $this->sync();
	}

	/**
	 * @return $this
     */
	public function save()
	{

		$this->getSource()->saveDatabase($this);

		return $this->sync();
	}

	/**
	 * @param array|null $data
	 *
	 * @return $this
     */
    public function create(array $data = null)
	{

		$this->getSource()->createDatabase($this);

		return $this->sync();
	}

	/**
	 * @return $this
     */
	public function remove()
	{

		$this->getSource()->removeDatabase($this);

		return $this->unsync();
	}

	/**
	 * @param bool|false $load
	 *
	 * @return \Generator
     */
    public function getTables($load = false)
	{

		foreach ($this->getSource()->getTableNames($this) as $name)
			yield $name => $this->getTable($name, $load);
	}

	/**
	 * @param bool|false $load
	 *
	 * @return array
     */
    public function getTableArray($load = false)
	{

		return iterator_to_array($this->getTables($load));
	}

	/**
	 * @param            $name
	 * @param bool|false $load
	 *
	 * @return \Tale\Data\Table
     */
    public function getTable($name, $load = false)
	{

		$config = $this->getSource()->getConfig();
		$className = $config->tableClassName;

		return new $className($this, $name, $load);
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