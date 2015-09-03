<?php

namespace Tale\Data;

use Tale\StringUtil;

class Database extends NamedEntityBase
{

	private $_source;

	public function __construct(Source $source, $name, $load = false)
	{
		parent::__construct($name);

		$this->_source = $source;

		if ($load)
			$this->load();
	}

	public function getSource()
	{

		return $this->_source;
	}

	public function exists()
	{

		return $this->getSource()->hasDatabase($this);
	}

	public function load()
	{

		$this->getSource()->loadDatabase($this);

		return $this->sync();
	}

	public function save()
	{

		$this->getSource()->saveDatabase($this);

		return $this->sync();
	}

	public function create(array $data = null)
	{

		$this->getSource()->createDatabase($this);

		return $this->sync();
	}

	public function remove()
	{

		$this->getSource()->removeDatabase($this);

		return $this->unsync();
	}

	public function getTables($load = false)
	{

		foreach ($this->getSource()->getTableNames($this) as $name)
			yield $name => $this->getTable($name, $load);
	}

	public function getTableArray($load = false)
	{

		return iterator_to_array($this->getTables($load));
	}

	public function getTable($name, $load = false)
	{

		$config = $this->getSource()->getConfig();
		$className = $config->tableClassName;

		if ($modelClassName = $this->getModelClassName($name))
			$className = $modelClassName;

		return new $className($this, $name, $load);
	}

	public function __get($name)
	{

		return $this->getTable($this->getSource()->inflectTableName($name));
	}
}