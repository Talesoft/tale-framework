<?php

namespace Tale\Data\Adapter;

//TODO: Implement table prefixes
//TODO: Documentation. ASAP!!

use Tale\Data\Pdo\AdapterBase;
use Tale\Data\Database,
	Tale\Data\Table,
	Tale\Data\Column,
	Tale\Data\Query,
	Exception;

class MySql extends AdapterBase
{

	private static $_typeMap = [
		'bool'   => 'tinyint',
		'byte'   => 'tinyint',
		'short'  => 'smallint',
		'long'   => 'bigint',
		'string' => 'varchar',
		'binary' => 'varbinary'
	];

	private static $_reverseTypeMap = [
		'tinyint'   => 'bool',
		'smallint'  => 'short',
		'bigint'    => 'long',
		'varchar'   => 'string',
		'varbinary' => 'binary',
		'text'      => 'string'
	];

	private $_preparedQueries;

	public function __construct(array $options = null)
	{

		$this->appendOptions([
			'driver'      => 'mysql',
			'user' => 'root',
			'password' => '',
			'data' => [
				'host' => 'localhost'
			],
			'inflections' => [
				'databases'     => 'Tale\\Util\\StringUtil::tableize',
				'tables'        => 'Tale\\Util\\StringUtil::tableize',
				'columns'       => 'Tale\\Util\\StringUtil::tableize',
				'inputColumns'  => 'Tale\\Util\\StringUtil::tableize',
				'outputColumns' => 'Tale\\Util\\StringUtil::variablize'
			]
		], true);

		parent::__construct($options);

		$this->prependOptions([
			'data'        => [
				'encoding' => 'utf8'
			],
			'collation'   => 'utf8_general_ci',
			'engine'      => 'InnoDB'
		], true);

		$this->_preparedQueries = [];
	}

	public function open()
	{

		parent::open();

		$this->query('SET NAMES ?', [$this->resolveOption('data.encoding')]);
	}


	public function prepare($query)
	{

		$hash = strlen($query) > 16 ? md5($query) : $query;

		if (isset($this->_preparedQueries[$hash]))
			return $this->_preparedQueries[$hash];

		$stmt = $this->getHandle()->prepare($query);
		$this->_preparedQueries[$hash] = $stmt;

		return $stmt;
	}

	public function query($query, array $args = null)
	{

		$args = $args ? $args : [];

		$stmt = $this->prepare($query);
		$stmt->execute($args);

		return $stmt;
	}

	public function quoteName()
	{

		$args = func_get_args();

		if (count($args) === 1) {

			$name = $args[0];

			return "`$name`";
		}

		return implode('.', array_map([$this, 'quoteName'], func_get_args()));
	}

	public function mapColumnType($type, $reverse = false)
	{

		$type = strtolower($type);

		if ($reverse) {

			if (array_key_exists($type, self::$_reverseTypeMap))
				return self::$_reverseTypeMap[$type];
		} else {

			if (array_key_exists($type, self::$_typeMap))
				return self::$_typeMap[$type];
		}

		return $type;
	}

	public function getColumnSql(Column $column)
	{

		$type = $column->getType();
		$maxLen = $column->getMaxLength();
		$allowed = $column->getAllowedValues();
		$optional = $column->isOptional();
		$default = $column->getDefaultValue();
		$primary = $column->isPrimary();
		$auto = $column->isAutoIncreased();

		if (!$type)
			throw new Exception("Failed to put together column SQL: Column $column has no type specified");

		$type = $this->mapColumnType($type);

		if ($type === 'varchar' && !$maxLen)
			$type = 'text';

		$sql = $this->quoteName($column);
		$sql .= ' '.strtoupper($type);

		if ($maxLen)
			$sql .= "($maxLen)";
		else if (!empty($allowed))
			$sql .= '('.implode(',', array_map([$this, 'encode'], $allowed)).')';

		if ($optional)
			$sql .= ' NULL';
		else
			$sql .= ' NOT NULL';

		if ($default)
			$sql .= ' DEFAULT '.$this->encode($default);

		if ($primary)
			$sql .= ' PRIMARY KEY';

		if ($auto)
			$sql .= ' AUTO_INCREMENT';

		return $sql;
	}

	public function encode($value)
	{

		return $this->getHandle()->quote($value);
	}

	public function decode($value)
	{

		return $value;
	}

	public function getDatabaseNames()
	{

		$stmt = $this->query('SHOW DATABASES');

		while ($name = $stmt->fetchColumn(0))
			yield $name;
	}

	public function hasDatabase(Database $database)
	{

		$stmt = $this->query('SHOW DATABASES WHERE `Database`=?', [$database]);

		return $stmt->fetchColumn(0) ? true : false;
	}

	public function loadDatabase(Database $database)
	{

		return $this;
	}

	public function saveDatabase(Database $database)
	{

		return $this;
	}

	public function createDatabase(Database $database)
	{

		$this->query('CREATE DATABASE '.$this->quoteName($database));

		return $this;
	}

	public function removeDatabase(Database $database)
	{

		$this->query('DROP DATABASE '.$this->quoteName($database));

		return $this;
	}


	public function getTableNames(Database $database)
	{

		$stmt = $this->query('SHOW TABLES IN '.$this->quoteName($database));

		while ($name = $stmt->fetchColumn(0))
			yield $name;
	}

	public function hasTable(Table $table)
	{

		$db = $this->quoteName($table->getDatabase());
		$col = $this->quoteName('Tables_in_'.$table->getDatabase());

		$stmt = $this->query("SHOW TABLES IN $db WHERE $col=?", [$table->getName()]);

		return $stmt->fetchColumn(0) ? true : false;
	}

	public function loadTable(Table $table)
	{

		return $this;
	}

	public function saveTable(Table $table)
	{

		return $this;
	}

	public function createTable(Table $table, array $columns)
	{

		if (!count($columns))
			throw new Exception("Failed to create table $table: No columns given");

		$cols = [];
		$extras = [];
		foreach ($columns as $col) {

			$cols[] = $this->getColumnSql($col);

			$idxName = $this->quoteName("{$col}_IDX");;

			if ($col->isUnique()) {

				$idxName = $this->quoteName("{$col}_UQ_IDX");
				$extras[] = "UNIQUE KEY $idxName(".$this->quoteName($col).')';
			}

			if ($col->isIndex())
				$extras[] = "INDEX $idxName(".$this->quoteName($col).')';

			$ref = null;
			if ($ref = $col->getReference()) {

				$fkName = $this->quoteName("{$table}_{$col}_FK");
				$refTbl = $ref->getTable();
				$refDb = $ref->getDatabase();
				$extras[] = "CONSTRAINT $fkName FOREIGN KEY("
					.$this->quoteName($col)
					.") REFERENCES ".$this->quoteName($refDb, $refTbl)."(".$this->quoteName($ref).")";
			}
		}

		$colSql = implode(',', array_merge($cols, $extras));
		$name = $this->quoteName($table->getDatabase(), $table);

		$this->query("CREATE TABLE $name($colSql) ENGINE=? COLLATE=?", [$this->getConfig()->engine, $this->getConfig()->collation]);

		return $this;
	}

	public function removeTable(Table $table)
	{

		/* It's important that we drop all CONSTRAINTs first, so we iterate the columns and save them without a reference (triggers saveColumn()) */
		/* We also need to drop all CONSTRAINTs, that reference THIS table. This will take a lot of performance right now */
		//TODO: OPTIMIZE PERFORMANCE!!!
		foreach ($table->getColumns()->loadAll() as $col) {
			$this->dropConstraint($col);
			$this->dropForeignConstraints($col);
		}

		$name = $this->quoteName($table->getDatabase(), $table);
		$this->query("DROP TABLE $name");

		return $this;
	}


	protected function getUniqueIndexName(Column $column, $quote = true)
	{

		$str = "{$column}_UQ_IDX";

		return $quote ? $this->quoteName($str) : $str;
	}

	protected function getIndexName(Column $column, $quote = true)
	{

		$str = "{$column}_IDX";

		return $quote ? $this->quoteName($str) : $str;
	}

	protected function getConstraintName(Column $column, $quote = true)
	{

		$table = $column->getTable();
		$str = "{$table}_{$column}_FK";

		return $quote ? $this->quoteName($str) : $str;
	}

	protected function addUniqueIndex(Column $column)
	{

		$table = $column->getTable();
		$tbl = $this->quoteName($table->getDatabase(), $table);
		$keyName = $this->getUniqueIndexName($column);
		$this->query("ALTER TABLE $tbl ADD UNIQUE KEY $keyName(".$this->quoteName($column).')');
	}

	protected function dropUniqueIndex(Column $column)
	{

		$table = $column->getTable();
		$tbl = $this->quoteName($table->getDatabase(), $table);
		$keyName = $this->getUniqueIndexName($column);
		$this->query("ALTER TABLE $tbl DROP INDEX $keyName");
	}

	protected function addIndex(Column $column)
	{

		$table = $column->getTable();
		$tbl = $this->quoteName($table->getDatabase(), $table);
		$keyName = $this->getIndexName($column);
		$this->query("ALTER TABLE $tbl ADD INDEX $keyName(".$this->quoteName($column).')');
	}

	protected function dropIndex(Column $column)
	{

		$table = $column->getTable();
		$tbl = $this->quoteName($table->getDatabase(), $table);
		$keyName = $this->getIndexName($column);
		$this->query("ALTER TABLE $tbl DROP INDEX $keyName");
	}

	protected function addConstraint(Column $column)
	{

		$ref = $column->getReference();

		if (!$ref)
			return;

		$table = $column->getTable();
		$fkName = $this->getConstraintName($column);
		$tbl = $this->quoteName($table->getDatabase(), $table);

		$refTbl = $ref->getTable();
		$refDb = $ref->getDatabase();
		$tblName = $this->quoteName($refDb, $refTbl);

		$this->query("ALTER TABLE $tbl ADD CONSTRAINT $fkName FOREIGN KEY("
			.$this->quoteName($column)
			.") REFERENCES $tblName(".$this->quoteName($ref).")");
	}

	protected function dropConstraint(Column $column, $dropIndex = false)
	{

		$ref = $column->getReference();

		if (!$ref)
			return;

		$table = $column->getTable();
		$fkName = $this->getConstraintName($column);
		$tbl = $this->quoteName($table->getDatabase(), $table);

		$this->query("ALTER TABLE $tbl DROP FOREIGN KEY $fkName");

		if ($dropIndex)
			$this->dropIndex($column);
	}

	protected function dropForeignConstraints(Column $column)
	{

		$table = $column->getTable();
		foreach ($table->getDatabase()->getTables() as $tbl)
			foreach ($tbl->getColumns()->loadAll() as $col) {

				$ref = $col->getReference();
				if ($ref && $ref->equals($column))
					$this->dropConstraint($col, true);
			}
	}


	public function getColumnNames(Table $table)
	{

		$stmt = $this->query('SHOW COLUMNS IN '.$this->quoteName($table->getDatabase(), $table));

		while ($name = $stmt->fetchColumn(0))
			yield $name;
	}

	public function hasColumn(Column $column)
	{

		$name = $this->quoteName($column->getDatabase(), $column->getTable());
		$stmt = $this->query("SHOW COLUMNS IN $name WHERE `Field`=?", [$column->getName()]);

		return $stmt->fetchColumn(0) ? true : false;
	}

	public function loadColumn(Column $column)
	{

		if ($column->isSynced())
			return $this;

		$name = $this->quoteName($column->getDatabase(), $column->getTable());
		$stmt = $this->query("SHOW COLUMNS IN $name WHERE `Field`=?", [$column->getName()]);

		$info = $stmt->fetchObject();

		$matches = [];
		if (!preg_match('/^(?<type>[a-zA-Z]+)(?:\((?<extra>[^\)]+)\))?$/i', $info->Type, $matches))
			throw new Exception("Received unexpected type {$info->Type} from database, failed to parse it.");

		$type = $this->mapColumnType(strtolower($matches['type']), true);
		$extra = isset($matches['extra']) ? $matches['extra'] : null;

		if ($extra) {

			if (is_numeric($extra)) {

				$maxLength = intval($extra);

				if ($type === 'bool' && $maxLength > 1)
					$type = 'byte';

				$column->setMaxLength($maxLength);
			} else {

				$column->setAllowedValues(array_map(function ($val) {

					return trim($val, '"\'');
				}, explode(',', $extra)));
			}
		}


		$column->setType($type);

		switch (strtolower($info->Null)) {
			case 'no':
				$column->makeRequired();
				break;
			default:
			case 'yes':
				$column->makeOptional();
				break;
		}

		switch (strtolower($info->Key)) {
			case 'pri':
				$column->makePrimary();
				break;
			case 'uni':
				$column->makeUnique();
				break;
			case 'mul':

				$table = $column->getTable();
				$fkName = "{$table}_{$column}_FK";
				$stmt = $this->query(
					'SELECT `REFERENCED_TABLE_SCHEMA` AS `db`, '
					.'`REFERENCED_TABLE_NAME` AS `tbl`, '
					.'`REFERENCED_COLUMN_NAME` AS `col` '
					.'FROM `information_schema`.`KEY_COLUMN_USAGE` '
					.'WHERE `CONSTRAINT_SCHEMA`=? AND `CONSTRAINT_NAME`=?',
					[$column->getDatabase()->getName(), $fkName]
				);

				$refInfo = $stmt->fetchObject();

				if ($info) {

					$refCol = $column->getSource()
						->getDatabase($refInfo->db)
						->getTable($refInfo->tbl)
						->getColumn($refInfo->col);
					$column->reference($refCol);
				} else
					$column->makeIndex();

				break;
		}

		if (!empty($info->Default))
			$column->setDefaultValue($info->Default);

		if ($info->Extra == 'auto_increment')
			$column->autoIncrease();

		return $this;
	}

	public function saveColumn(Column $column)
	{

		if ($column->isSynced())
			return $this;

		$syncedCol = $column->getTable()->getColumn($column->getName())->load();

		if ($column->equals($syncedCol, false))
			return $this;

		$sql = $this->getColumnSql($column);
		$name = $this->quoteName($column->getDatabase(), $column->getTable());

		if ($syncedCol->isUnique() && !$column->isUnique())
			$this->dropUniqueIndex($syncedCol);

		$this->query("ALTER TABLE $name MODIFY $sql");

		/* Drop/Add UNIQUE if needed */
		if (!$syncedCol->isUnique() && $column->isUnique())
			$this->addUniqueIndex($column);

		/* Drop PRIMARY if needed (It gets added through getColumnSql() and the PRIMARY KEY addon) */
		if ($syncedCol->isPrimary() && !$column->isPrimary())
			$this->query("ALTER TABLE $name DROP PRIMARY KEY");

		/* Add INDEX if needed */
		if (!$syncedCol->isIndex() && $column->isIndex())
			$this->addIndex($column);

		/* Drop/Add CONSTRAINTS if needed */
		$syncedRef = $syncedCol->getReference();
		$colRef = $column->getReference();
		$refChanged = ($colRef && $syncedRef
			&& !$colRef->equals($syncedRef));
		if (($syncedRef && !$colRef)
			|| ($colRef && !$syncedRef)
			|| $refChanged
		) {

			if (($syncedRef && !$colRef) || $refChanged)
				$this->dropConstraint($syncedCol);

			if (($colRef && !$syncedRef) || $refChanged)
				$this->addConstraint($column);
		}

		/* Drop INDEX if needed */
		if ($syncedCol->isIndex() && !$column->isIndex())
			$this->dropIndex($column);


		return $this;
	}

	public function createColumn(Column $column)
	{

		if ($column->isSynced(true))
			return $this;

		$sql = $this->getColumnSql($column);
		$name = $this->quoteName($column->getDatabase(), $column->getTable());

		$this->query("ALTER TABLE $name ADD $sql");

		if ($column->isUnique())
			$this->addUniqueIndex($column);

		/* Add INDEX if needed */
		if ($column->isIndex())
			$this->addIndex($column);

		$this->addConstraint($column);

		return $this;
	}

	public function removeColumn(Column $column)
	{

		$name = $this->quoteName($column->getDatabase(), $column->getTable());

		if (!$column->isSynced())
			$column->load();

		/* If this column has a reference, we need to drop it first */
		$this->dropConstraint($column);
		$this->dropForeignConstraints($column);

		$this->query("ALTER TABLE $name DROP ".$this->quoteName($column));

		return $this;
	}


	protected function parseClauses(array $clauses, $joinWith = 'AND')
	{

		$checks = [];
		$args = [];
		foreach ($clauses as $field => $value) {

			$suffix = '';
			$len = strlen($field);
			$char = null;
			while (!ctype_alnum($char = $field[--$len]))
				if ($char !== '.')
					$suffix = $char.$suffix;

			$field = $this->inflectInputColumnName(substr($field, 0, $len + 1));

			if (in_array($field, ['or', 'and'])) {

				list($sql, $subArgs) = $this->parseClauses($value, strtoupper($field));
				$checks[] = "($sql)";
				$args = array_merge($args, $subArgs);

				continue;
			}

			$negate = ($suffix === '!');
			$op = '=';

			if (is_object($value))
				$value = (array)$value;

			if (is_array($value)) {

				$checks[] = "`$field` ".($negate ? 'NOT ' : '').'IN('.implode(',', array_fill(0, count($value), '?')).')';
				foreach ($value as $v)
					$args[] = $v;

				continue;
			}

			switch ($suffix) {
				case '!':
					$op = '!=';
					break;
				case '~':
				case '*':
				case '^':
				case '$':
					$op = ' LIKE ';

					$left = ($suffix !== '^' ? '%' : '');
					$right = ($suffix !== '$' ? '%' : '');

					$value = "$left$value$right";
					break;
				case '>':
				case '>=':
				case '<=':
				case '<':

					$op = $suffix;
					break;
			}

			$checks[] = "`$field`$op?";
			$args[] = $value;
		}

		return [implode(" $joinWith ", $checks), $args];
	}

	protected function parseSelectFields(array $fields)
	{

		$result = [];
		$index = null;
		foreach ($fields as $name => $alias) {

			$col = $name;
			if (is_int($name)) {

				$col = $alias;
				$alias = null;
			}

			if ($col[0] === '$') {

				$col = substr($col, 1);
				$index = $col;
			}

			$col = $this->inflectInputColumnName($col);

			if ($alias)
				$alias = $this->inflectInputColumnName($alias);

			$result[] = "`$col`".($alias ? " AS `$alias`" : '');
		}

		return [implode(',', $result), $index];
	}

	protected function parse(Query $qry)
	{

		$sql = '';
		$args = [];
		$clauses = $qry->getClauses();
		if (count($clauses)) {

			list($clauseSql, $clauseArgs) = $this->parseClauses($clauses);

			$sql .= " WHERE $clauseSql";
			$args = array_merge($args, $clauseArgs);
		}

		if ($qry->isRandomSorted()) {
			$sql .= " ORDER BY RAND()";
		} else {

			$sorts = $qry->getSortings();
			if (count($sorts)) {

				$sortings = [];
				foreach ($sorts as $field => $direction) {

					if (is_int($field)) {

						$field = $direction;
						$direction = 'asc';
					}

					switch (strtolower($direction)) {
						case 'asc':
						case 'ascending':
						case '+':
						case '>':
						case 'v':
						default:
							$direction = 'ASC';
							break;
						case 'desc':
						case 'descending':
						case '-':
						case '<':
						case '^':
							$direction = 'DESC';
							break;
					}

					$sortings[] = '`'.$this->inflectInputColumnName($field)."` $direction";
				}

				$sql .= " ORDER BY ".implode(',', $sortings);
			}
		}


		$limit = $qry->getLimit();
		$limitStart = $qry->getLimitStart();

		if (!is_null($limit)) {

			$limit = intval($limit);
			$limitStart = intval($limitStart ? $limitStart : 0);

			$sql .= " LIMIT $limitStart,$limit";
		}

		return [$sql, $args];
	}

	protected function parseData(array $data)
	{

		$items = [];
		$args = [];

		foreach ($data as $key => $val) {

			$items[] = $this->quoteName($this->inflectInputColumnName($key)).'=?';
			$args[] = $val;
		}

		$sql = ' SET '.implode(',', $items);

		return [$sql, $args];
	}

	protected function parseRow(array $row)
	{

		foreach ($row as $name => $value) {

			$inflectedName = $this->inflectOutputColumnName($name);
			yield $inflectedName => $value;
		}
	}

	protected function processRow(Table $table, array $data, $as = null)
	{

		$inflectedRow = iterator_to_array($this->parseRow($data));

		if ($as === false)
			return $inflectedRow;

		if ($as === null)
			$as = 'Tale\\Data\\Row';

		return new $as($table, $inflectedRow);
	}

	public function countRows(Query $query, $field = null, $distinct = false)
	{

		list($sql, $args) = $this->parse($query);
		$table = $query->getTable();
		$name = $this->quoteName($table->getDatabase(), $table);

		$countedField = '*';

		if ($field)
			$countedField = "`$field`";

		if ($distinct)
			$countedField = "DISTINCT $countedField";

		$qry = "SELECT COUNT($countedField) FROM $name$sql";

		$stmt = $this->query($qry, $args);

		return intval($stmt->fetchColumn(0));
	}

	public function loadRows(Query $query, array $fields = null, $as = null)
	{

		list($sql, $args) = $this->parse($query);
		$table = $query->getTable();
		$name = $this->quoteName($table->getDatabase(), $table);

		list($fields, $index) = $fields ? $this->parseSelectFields($fields) : ['*', null];
		$qry = "SELECT $fields FROM $name$sql";

		$stmt = $this->query($qry, $args);
		$stmt->setFetchMode(\PDO::FETCH_ASSOC);

		while ($row = $stmt->fetch())
			if ($index) {

				$indexValue = $row[$index];
				yield $indexValue => $this->processRow($table, $row, $as);
			} else
				yield $this->processRow($table, $row, $as);
	}

	public function saveRows(Query $query, array $data)
	{

		list($sql, $args) = $this->parse($query);
		$table = $query->getTable();
		$name = $this->quoteName($table->getDatabase(), $table);

		list($updateSql, $updateArgs) = $this->parseData($data);

		$qry = "UPDATE $name $updateSql$sql";

		$this->query($qry, array_merge($updateArgs, $args));

		return $this;
	}

	public function createRow(Table $table, array $data)
	{

		$name = $this->quoteName($table->getDatabase(), $table);

		list($sql, $args) = $this->parseData($data);

		$qry = "INSERT INTO $name$sql";

		$this->query($qry, $args);

		return $this;
	}

	public function removeRows(Query $query)
	{

		list($sql, $args) = $this->parse($query);
		$table = $query->getTable();
		$name = $this->quoteName($table->getDatabase(), $table);

		$qry = "DELETE FROM $name$sql";

		$this->query($qry, $args);

		return $this;
	}

	public function getLastId()
	{

		$stmt = $this->query('SELECT LAST_INSERT_ID()');

		return $stmt->fetchColumn(0);
	}
}
