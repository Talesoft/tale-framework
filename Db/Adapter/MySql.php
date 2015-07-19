<?php

namespace Tale\Db\Adapter;

use Tale\Db\AdapterBase,
    Tale\Db\Database,
    Tale\Db\Table,
    Tale\Db\Column,
    Tale\Db\Controller\DatabaseController,
    Tale\Db\Controller\TableController,
    PDO;
use Tale\Db\Query;

class MySql extends AdapterBase {

    private static $_typeMap = [
        'bool' => 'tinyint',
        'byte' => 'tinyint',
        'short' => 'smallint',
        'long' => 'bigint',
        'string' => 'varchar',
        'binary' => 'varbinary'
    ];

    private static $_reverseTypeMap = [
        'tinyint' => 'bool',
        'smallint' => 'short',
        'bigint' => 'long',
        'varchar' => 'string',
        'varbinary' => 'binary',
        'text' => 'string'
    ];

    private $_pdo;
    private $_preparedQueries;

    public function __construct( array $options = null ) {
        parent::__construct( array_replace_recursive( [
            'host' => 'localhost',
            'user' => 'root',
            'password' => '',
            'encoding' => 'utf8',
            'collation' => 'utf8_general_ci',
            'engine' => 'InnoDB',
            'databaseStyle' => 'Tale\\StringUtils::tableize',
            'tableStyle' => 'Tale\\StringUtils::tableize',
            'columnStyle' => 'Tale\\StringUtils::tableize',
            'inputColumnStyle' => 'Tale\\StringUtils::tableize',
            'outputColumnStyle' => 'Tale\\StringUtils::variablize'
        ], $options ? $options : [] ) );

        $config = $this->getConfig();

        $this->_pdo = new PDO( "mysql:host={$config->host}", $config->user, $config->password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_STATEMENT_CLASS => [ 'Tale\\Data\\Adapter\\MySql\\Statement', [] ],
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config->encoding} COLLATE {$config->collation}"
        ] );
        $this->_preparedQueries = [];
    }

    protected function prepare( $query ) {

        $hash = strlen( $query ) > 30 ? md5( $query ) : $query;

        if( isset( $this->_preparedQueries[ $hash ] ) )
            return $this->_preparedQueries[ $hash ];

        $stmt = $this->_pdo->prepare( $query );
        $this->_preparedQueries[ $hash ] = $stmt;

        return $stmt;
    }

    protected function query( $query, array $args = null ) {

        $args = $args ? $args : [];

        var_dump( "EXEC $query", $args );

        $stmt = $this->prepare( $query );
        $stmt->execute( $args );

        return $stmt;
    }

    protected function quoteNames() {

        $args = func_get_args();
        if( count( $args ) === 1 ) {

            $name = $args[ 0 ];
            return "`$name`";
        }

        return implode( '.', array_map( [ $this, 'quoteNames' ], func_get_args() ) );
    }

    public function getDatabases() {

        $stmt = $this->query( 'SHOW DATABASES' );

        $databases = [];
        while( $name = $stmt->fetchColumn( 0 ) )
            $databases[ $name ] = new Database( $name );

        return $databases;
    }

    public function createDatabase( Database $database ) {

        $this->query( 'CREATE DATABASE '.$this->quoteNames( $database ) );

        return $this;
    }

    public function hasDatabase( Database $database ) {

        return count( $this->query(
            'SHOW DATABASES LIKE ?',
            [ $database ]
        )->fetchAll() ) > 0;
    }

    public function loadDatabase( Database $database ) {

        return $this;
    }

    public function saveDatabase( Database $database ) {

        return $this;
    }

    public function removeDatabase( Database $database ) {

        $this->query( 'DROP DATABASE '.$this->quoteNames( $database ) );

        return $this;
    }

    public function getTables( Database $database ) {

        $stmt = $this->query( 'SHOW TABLES IN '.$this->quoteNames( $database ) );
        $tables = [];
        while( $name = $stmt->fetchColumn( 0 ) )
            $tables[ $name ] = new Table( $name );

        return $tables;
    }

    public function createTable( Database $database, Table $table ) {


    }

    public function hasTable( Database $database, Table $table ) {

        return count( $this->query(
            'SHOW TABLES IN '.$this->quoteNames( $database ).' LIKE ?',
            [ $table ]
        )->fetchAll() ) > 0;
    }

    public function loadTable( Database $database, $name ) {

        return $this;
    }

    public function saveTable( Database $database, Table $table ) {

        return $this;
    }

    public function removeTable( Database $database, Table $table ) {

        $this->query( 'DROP TABLE '.$this->quoteNames( $database, $table ) );

        return $this;
    }

    public function getColumns( Database $database, Table $table ) {

        $stmt = $this->query( 'SHOW COLUMNS FROM '.$this->quoteName( $database, $table ) );

        while( $row = $stmt->fetch() )
            var_dump( $row );
    }

    public function createColumn( Database $database, Table $table, Column $column ) {
        // TODO: Implement createColumn() method.
    }

    public function hasColumn( Database $database, Table $table, Column $column ) {
        // TODO: Implement hasColumn() method.
    }

    public function loadColumn( Database $database, Table $table, Column $column ) {
        // TODO: Implement loadColumn() method.
    }

    public function saveColumn( Database $database, Table $table, Column $column ) {
        // TODO: Implement saveColumn() method.
    }

    public function removeColumn( Database $database, Table $table, Column $column ) {
        // TODO: Implement removeColumn() method.
    }

    public function createRow( Database $database, Table $table, $data ) {
        // TODO: Implement createRow() method.
    }

    public function countRows( Database $database, Table $table, Query $query = null ) {
        // TODO: Implement countRows() method.
    }

    public function getRows( Database $database, Table $table, $what, Query $query = null ) {
        // TODO: Implement getRows() method.
    }

    public function saveRows( Database $database, Table $table, $data, Query $query = null ) {
        // TODO: Implement saveRows() method.
    }

    public function removeRows( Database $database, Table $table, Query $query = null ) {
        // TODO: Implement removeRows() method.
    }

    public function getDatabaseController( $databaseName ) {

        return parent::getDatabaseController( call_user_func( $this->getConfig()->databaseStyle, $databaseName ) );
    }

    public function getTableController( DatabaseController $databaseController, $tableName ) {

        return parent::getTableController( $databaseController, call_user_func( $this->getConfig()->databaseStyle, $tableName ) );
    }

    public function getColumnController( TableController $tableController, $columnName ) {

        return parent::getColumnController( $tableController, call_user_func( $this->getConfig()->databaseStyle, $columnName ) );
    }







    protected function mapColumnType( $type, $reverse = false ) {

        $type = strtolower( $type );

        if( $reverse ) {

            if( isset( self::$_reverseTypeMap[ $type ] ) )
                return self::$_reverseTypeMap[ $type ];
        } else {

            if( isset( self::$_typeMap[ $type ] ) )
                return self::$_typeMap[ $type ];
        }

        return $type;
    }

    protected function getColumnSql( Column $column ) {

        $type = $column->getType();
        $maxLen = $column->getMaxLength();
        $allowed = $column->getAllowedValues();
        $optional = $column->isOptional();
        $default = $column->getDefaultValue();
        $primary = $column->isPrimary();
        $auto = $column->isAutoIncreased();

        if( !$type )
            throw new Exception( "Failed to put together column SQL: Column $column has no type specified" );

        $type = $this->mapColumnType( $type );

        if( $type === 'varchar' && !$maxLen )
            $type = 'text';

        $sql = $this->quoteNames( $column );
        $sql .= ' '.strtoupper( $type );

        if( $maxLen )
            $sql .= "($maxLen)";
        else if( !empty( $allowed ) )
            $sql .= '('.implode( ',', array_map( [ $this, 'encode' ], $allowed ) ).')';

        if( $optional )
            $sql .= ' NULL';
        else
            $sql .= ' NOT NULL';

        if( $default )
            $sql .= ' DEFAULT '.$this->encode( $default );

        if( $primary )
            $sql .= ' PRIMARY KEY';

        if( $auto )
            $sql .= ' AUTO_INCREMENT';

        return $sql;
    }


    /*


    public function encode( $value ) {

        return $this->getPdoHandle()->quote( $value );
    }

    public function decode( $value ) {

        return $value;
    }

    public function getDatabaseNames() {

        $stmt = $this->query( 'SHOW DATABASES' );

        while( $name = $stmt->fetchColumn( 0 ) )
            yield $name;
    }

    public function hasDatabase( DatabaseInterface $database ) {

        $stmt = $this->query( 'SHOW DATABASES WHERE `Database`=?', [ $database->getName() ] );

        return $stmt->fetchColumn( 0 ) ? true : false;
    }

    public function loadDatabase( DatabaseInterface $database ) {

        if( !$database->exists() )
            throw new Exception( "Failed to load database $database: Database does not exist. Use exists() and create() to solve this." );

        return $this;
    }

    public function saveDatabase( DatabaseInterface $database ) {

        if( !$database->exists() )
            throw new Exception( "Failed to save database $database: Database does not exist. Use exists() and create() to solve this." );

        return $this;
    }

    public function createDatabase( DatabaseInterface $database ) {

        if( $database->exists() )
            throw new Exception( "Failed to create database $database: Database already exists. Use exists() to solve this." );

        $this->query( 'CREATE DATABASE '.$this->quoteName( $database ) );

        return $this;
    }

    public function removeDatabase( DatabaseInterface $database ) {

        if( !$database->exists() )
            throw new Exception( "Failed to remove database $database: Database doesnt exist. Use exists() to solve this." );

        $this->query( 'DROP DATABASE '.$this->quoteName( $database ) );

        return $this;
    }




    public function getTableNames( DatabaseInterface $database ) {

        $stmt = $this->query( 'SHOW TABLES IN '.$this->quoteName( $database ) );

        while( $name = $stmt->fetchColumn( 0 ) )
            yield $name;
    }

    public function hasTable( TableInterface $table ) {

        $db = $this->quoteName( $table->getDatabase() );
        $col = $this->quoteName( 'Tables_in_'.$table->getDatabase() );

        $stmt = $this->query( "SHOW TABLES IN $db WHERE $col=?", [ $table->getName() ] );

        return $stmt->fetchColumn( 0 ) ? true : false;
    }

    public function loadTable( TableInterface $table ) {

        if( !$table->exists() )
            throw new Exception( "Failed to load table $table: Table does not exist. Use exists() and create() to solve this." );

        return $this;
    }

    public function saveTable( TableInterface $table ) {

        if( !$table->exists() )
            throw new Exception( "Failed to save table $table: Table does not exist. Use exists() and create() to solve this." );

        return $this;
    }

    public function createTable( TableInterface $table, array $columns ) {

        if( $table->exists() )
            throw new Exception( "Failed to create table $table: Table does already exist. Use exists() to solve this." );

        $cols = [];
        $extras = [];
        foreach( $columns as $col ) {

            $cols[] = $this->getColumnSql( $col );

            $idxName = $this->quoteName( "{$col}_IDX" );;

            if( $col->isUnique() ) {

                $idxName = $this->quoteName( "{$col}_UQ_IDX" );
                $extras[] = "UNIQUE KEY $idxName(".$this->quoteName( $col ).')';
            }

            if( $col->isIndex() )
                $extras[] = "INDEX $idxName(".$this->quoteName( $col ).')';

            $ref = null;
            if( $ref = $col->getReference() ) {

                $fkName = $this->quoteName( "{$table}_{$col}_FK" );
                $refTbl = $ref->getTable();
                $refDb = $ref->getDatabase();
                $extras[] = "CONSTRAINT $fkName FOREIGN KEY("
                            .$this->quoteName( $col )
                            .") REFERENCES ".$this->quoteName( $refDb, $refTbl )."(".$this->quoteName( $ref ).")";
            }
        }

        $colSql = implode( ',', ArrayUtils::concat( $cols, $extras ) );
        $name = $this->quoteName( $table->getDatabase(), $table );

        $this->query( "CREATE TABLE $name($colSql) ENGINE=? COLLATE=?", [ $this->getOption( 'tableEngine' ), $this->getOption( 'tableEncoding' ) ] );

        return $this;
    }

    public function removeTable( TableInterface $table ) {

        if( !$table->exists() )
            throw new Exception( "Failed to remove table $table: Table does not exist. Use exists() to solve this." );

        / * It's important that we drop all CONSTRAINTs first, so we iterate the columns and save them without a reference (triggers saveColumn()) * /
        /* We also need to drop all CONSTRAINTs, that reference THIS table. This will take a lot of performance right now * /
        //TODO: OPTIMIZE PERFORMANCE!!!
        foreach( $table->getColumns( true ) as $col ) {
            $this->dropConstraint( $col );
            $this->dropForeignConstraints( $col );
        }

        $name = $this->quoteName( $table->getDatabase(), $table );
        $this->query( "DROP TABLE $name" );

        return $this;
    }


    protected function getUniqueIndexName( ColumnInterface $column, $quote = true ) {

        $str = "{$column}_UQ_IDX";
        return $quote ? $this->quoteName( $str ) : $str;
    }

    protected function getIndexName( ColumnInterface $column, $quote = true ) {

        $str = "{$column}_IDX";
        return $quote ? $this->quoteName( $str ) : $str;
    }

    protected function getConstraintName( ColumnInterface $column, $quote = true ) {

        $table = $column->getTable();
        $str = "{$table}_{$column}_FK";
        return $quote ? $this->quoteName( $str ) : $str;
    }

    protected function addUniqueIndex( ColumnInterface $column ) {

        $table = $column->getTable();
        $tbl = $this->quoteName( $table->getDatabase(), $table );
        $keyName = $this->getUniqueIndexName( $column );
        $this->query( "ALTER TABLE $tbl ADD UNIQUE KEY $keyName(".$this->quoteName( $column ).')' );
    }

    protected function dropUniqueIndex( ColumnInterface $column ) {

        $table = $column->getTable();
        $tbl = $this->quoteName( $table->getDatabase(), $table );
        $keyName = $this->getUniqueIndexName( $column );
        $this->query( "ALTER TABLE $tbl DROP INDEX $keyName" );
    }

    protected function addIndex( ColumnInterface $column ) {

        $table = $column->getTable();
        $tbl = $this->quoteName( $table->getDatabase(), $table );
        $keyName = $this->getIndexName( $column );
        $this->query( "ALTER TABLE $tbl ADD INDEX $keyName(".$this->quoteName( $column ).')' );
    }

    protected function dropIndex( ColumnInterface $column ) {

        $table = $column->getTable();
        $tbl = $this->quoteName( $table->getDatabase(), $table );
        $keyName = $this->getIndexName( $column );
        $this->query( "ALTER TABLE $tbl DROP INDEX $keyName" );
    }

    protected function addConstraint( ColumnInterface $column ) {

        $ref = $column->getReference();

        if( !$ref )
            return;

        $table = $column->getTable();
        $fkName = $this->getConstraintName( $column );
        $tbl = $this->quoteName( $table->getDatabase(), $table );

        $refTbl = $ref->getTable();
        $refDb = $ref->getDatabase();
        $tblName = $this->quoteName( $refDb, $refTbl );

        $this->query( "ALTER TABLE $tbl ADD CONSTRAINT $fkName FOREIGN KEY("
                      .$this->quoteName( $column )
                      .") REFERENCES $tblName(".$this->quoteName( $ref ).")" );
    }

    protected function dropConstraint( ColumnInterface $column, $dropIndex = false ) {

        $ref = $column->getReference();

        if( !$ref )
            return;

        $table = $column->getTable();
        $fkName = $this->getConstraintName( $column );
        $tbl = $this->quoteName( $table->getDatabase(), $table );

        $this->query( "ALTER TABLE $tbl DROP FOREIGN KEY $fkName" );

        if( $dropIndex )
            $this->dropIndex( $column );
    }

    protected function dropForeignConstraints( ColumnInterface $column ) {

        $table = $column->getTable();
        foreach( $table->getDatabase()->getTables() as $tbl )
            foreach( $tbl->getColumns( true ) as $col ) {

                $ref = $col->getReference();
                if( $ref && $ref->equals( $column ) )
                    $this->dropConstraint( $col, true );
            }
    }


    public function getColumnNames( TableInterface $table ) {

        $stmt = $this->query( 'SHOW COLUMNS IN '.$this->quoteName( $table->getDatabase(), $table ) );

        while( $name = $stmt->fetchColumn( 0 ) )
            yield $name;
    }

    public function hasColumn( ColumnInterface $column ) {

        $name = $this->quoteName( $column->getDatabase(), $column->getTable() );
        $stmt = $this->query( "SHOW COLUMNS IN $name WHERE `Field`=?", [ $column->getName() ] );

        return $stmt->fetchColumn( 0 ) ? true : false;
    }

    public function loadColumn( ColumnInterface $column ) {

        if( !$column->exists() )
            throw new Exception( "Failed to load column $column: Column does not exist. Use exists() and create() to solve this." );

        if( $column->isSynced() )
            return $this;

        $name = $this->quoteName( $column->getDatabase(), $column->getTable() );
        $stmt = $this->query( "SHOW COLUMNS IN $name WHERE `Field`=?", [ $column->getName() ] );

        $info = $stmt->fetchObject();

        $matches = [];
        if( !preg_match( '/^(?<type>[a-zA-Z]+)(?:\((?<extra>[^\)]+)\))?$/i', $info->Type, $matches ) )
            throw new Exception( "Received unexpected type {$info->Type} from database, failed to parse it." );

        $type = $this->mapColumnType( strtolower( $matches[ 'type' ] ), true );
        $extra = isset( $matches[ 'extra' ] ) ? $matches[ 'extra' ] : null;

        if( $extra ) {

            if( is_numeric( $extra ) ) {

                $maxLength = intval( $extra );

                if( $type === 'bool' && $extra > 1 )
                    $type = 'byte';

                $column->setMaxLength( $extra );
            } else {

                $column->setAllowedValues( array_map( function( $val ) {

                    return trim( $val, '"\'' );
                }, explode( ',', $extra ) ) );
            }
        }


        $column->setType( $type );

        switch( strtolower( $info->Null ) ) {
            case 'no': $column->makeRequired(); break;
            default:
            case 'yes': $column->makeOptional(); break;
        }

        switch( strtolower( $info->Key ) ) {
            case 'pri': $column->makePrimary(); break;
            case 'uni': $column->makeUnique(); break;
            case 'mul':

                $table = $column->getTable();
                $fkName = "{$table}_{$column}_FK";
                $stmt = $this->query( 'SELECT `REFERENCED_TABLE_SCHEMA` AS `db`, `REFERENCED_TABLE_NAME` AS `tbl`, `REFERENCED_COLUMN_NAME` AS `col` FROM `information_schema`.`KEY_COLUMN_USAGE` WHERE `CONSTRAINT_SCHEMA`=? AND `CONSTRAINT_NAME`=?', [ $column->getDatabase()->getName(), $fkName ] );

                $refInfo = $stmt->fetchObject();

                if( $info ) {

                    $refCol = $column->getDataSource()
                                     ->getDatabase( $refInfo->db )
                                     ->getTable( $refInfo->tbl )
                                     ->getColumn( $refInfo->col );
                    $column->reference( $refCol );
                } else
                    $column->makeIndex();

                break;
        }

        if( !empty( $info->Default ) )
            $column->setDefaultValue( $info->Default );

        if( $info->Extra == 'auto_increment' )
            $column->autoIncrease();

        return $this;
    }

    public function saveColumn( ColumnInterface $column ) {

        if( !$column->exists() )
            throw new Exception( "Failed to save column $column: Column doesnt exist. Use exists() to solve this." );

        if( $column->isSynced() )
            return $this;

        $syncedCol = $column->getTable()->getColumn( $column->getName(), true );

        if( $column->equals( $syncedCol, false ) )
            return $this;

        $sql = $this->getColumnSql( $column );
        $name = $this->quoteName( $column->getDatabase(), $column->getTable() );

        if( $syncedCol->isUnique() && !$column->isUnique() )
            $this->dropUniqueIndex( $syncedCol );

        $this->query( "ALTER TABLE $name MODIFY $sql" );

        /* Drop/Add UNIQUE if needed * /
        if( !$syncedCol->isUnique() && $column->isUnique() )
            $this->addUniqueIndex( $column );

        /* Drop PRIMARY if needed (It gets added through getColumnSql() and the PRIMARY KEY addon) * /
        if( $syncedCol->isPrimary() && !$column->isPrimary() )
            $this->query( "ALTER TABLE $name DROP PRIMARY KEY" );

        /* Add INDEX if needed * /
        if( !$syncedCol->isIndex() && $column->isIndex() )
            $this->addIndex( $column );

        /* Drop/Add CONSTRAINTS if needed * /
        $syncedRef = $syncedCol->getReference();
        $colRef = $column->getReference();
        $refChanged = ( $colRef && $syncedRef
                        && !$colRef->equals( $syncedRef ) );
        if( ( $syncedRef && !$colRef )
            || ( $colRef && !$syncedRef )
            || $refChanged ) {

            if( ( $syncedRef && !$colRef ) || $refChanged )
                $this->dropConstraint( $syncedCol );

            if( ( $colRef && !$syncedRef ) || $refChanged )
                $this->addConstraint( $column );
        }

        /* Drop INDEX if needed * /
        if( $syncedCol->isIndex() && !$column->isIndex() )
            $this->dropIndex( $column );


        return $this;
    }

    public function createColumn( ColumnInterface $column ) {

        if( $column->exists() )
            throw new Exception( "Failed to create column $column: Column already exists. Use exists() to solve this." );

        $sql = $this->getColumnSql( $column );
        $name = $this->quoteName( $column->getDatabase(), $column->getTable() );

        $this->query( "ALTER TABLE $name ADD $sql" );

        if( $column->isUnique() )
            $this->addUniqueIndex( $column );

        /* Add INDEX if needed * /
        if( $column->isIndex() )
            $this->addIndex( $column );

        $this->addConstraint( $column );

        return $this;
    }

    public function removeColumn( ColumnInterface $column ) {

        if( !$column->exists() )
            throw new Exception( "Failed to remove column $column: Column doesnt exist. Use exists() to solve this." );

        $name = $this->quoteName( $column->getDatabase(), $column->getTable() );

        if( !$column->isSynced() )
            $column->load();

        /* If this column has a reference, we need to drop it first * /
        $this->dropConstraint( $column );
        $this->dropForeignConstraints( $column );

        $this->query( "ALTER TABLE $name DROP ".$this->quoteName( $column ) );

        return $this;
    }


    protected function parseClauses( array $clauses, $joinWith = 'AND' ) {

        $checks = [];
        $args = [];
        foreach( $clauses as $field => $value ) {

            $suffix = '';
            $len = strlen( $field );
            $char = null;
            while( !ctype_alnum( $char = $field[ --$len ] ) )
                if( $char !== '.' )
                    $suffix = $char.$suffix;

            $field = $this->inflectInputColumnName( substr( $field, 0, $len + 1 ) );

            if( in_array( $field, [ 'or', 'and' ] ) ) {

                list( $sql, $subArgs ) = $this->parseClauses( $value, strtoupper( $field ) );
                $checks[] = "($sql)";
                $args = ArrayUtils::concat( $args, $subArgs );

                continue;
            }

            $negate = ( $suffix === '!' );

            $op = '=';


            if( is_object( $value ) )
                $value = (array)$value;

            if( is_array( $value ) ) {

                $checks[] = "`$field` ".( $negate ? 'NOT ' : '' ).'IN('.implode( ',', array_fill( 0, count( $value ), '?' ) ).')';
                foreach( $value as $v )
                    $args[] = $v;

                continue;
            }

            switch( $suffix ) {
                case '!': $op = '!='; break;
                case '~':
                case '*':
                case '^':
                case '$':
                    $op = ' LIKE ';

                    $left = ( $suffix !== '^' ? '%' : '' );
                    $right = ( $suffix !== '$' ? '%' : '' );

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

        return [ implode( " $joinWith ", $checks ), $args ];
    }

    protected function parseSelectFields( array $fields ) {

        $result = [];
        $index = null;
        foreach( $fields as $name => $alias ) {

            $col = $name;
            if( is_int( $name ) ) {

                $col = $alias;
                $alias = null;
            }

            if( $col[ 0 ] === '$' ) {

                $col = substr( $col, 1 );
                $index = $col;
            }

            $col = $this->inflectInputColumnName( $col );

            if( $alias )
                $alias = $this->inflectInputColumnName( $alias );

            $result[] = "`$col`".( $alias ? " AS `$alias`" : '' );
        }

        return [ implode( ',', $result ), $index ];
    }

    protected function parse( QueryInterface $qry ) {

        $sql = '';
        $args = [];
        $clauses = $qry->getClauses();
        if( count( $clauses ) ) {

            list( $clauseSql, $clauseArgs ) = $this->parseClauses( $clauses );

            $sql .= " WHERE $clauseSql";
            $args = ArrayUtils::concat( $args, $clauseArgs );
        }

        if( $qry->isRandomSorted() ) {
            $sql .= " ORDER BY RAND()";
        } else {

            $sorts = $qry->getSortings();
            if( count( $sorts ) ) {

                $sortings = [];
                foreach( $sorts as $field => $direction ) {

                    if( is_int( $field ) ) {

                        $field = $direction;
                        $direction = 'asc';
                    }

                    switch( strtolower( $direction ) ) {
                        case 'asc':
                        case 'ascending':
                        case '+':
                        case '<':
                        default:
                            $direction = 'ASC';
                            break;
                        case 'desc':
                        case 'descending':
                        case '-':
                        case '>':
                            $direction = 'DESC';
                            break;
                    }

                    $sortings[] = '`'.$this->inflectInputColumnName( $field )."` $direction";
                }

                $sql .= " ORDER BY ".implode( ',', $sortings );
            }
        }


        $limit = $qry->getLimit();
        $limitStart = $qry->getLimitStart();

        if( !is_null( $limit ) ) {

            $limit = intval( $limit );
            $limitStart = intval( $limitStart ? $limitStart : 0 );

            $sql .= " LIMIT $limitStart,$limit";
        }

        return [ $sql, $args ];
    }

    protected function parseData( array $data ) {

        $items = [];
        $args = [];

        foreach( $data as $key => $val ) {

            $items[] = $this->quoteName( $this->inflectInputColumnName( $key ) ).'=?';
            $args[] = $val;
        }

        $sql = ' SET '.implode( ',', $items );

        return [ $sql, $args ];
    }

    protected function inflectRow( array $row ) {

        foreach( $row as $name => $value ) {

            $inflectedName = $this->inflectOutputColumnName( $name );

            yield $inflectedName => $value;
        }
    }

    protected function processRow( TableInterface $table, array $data, $as = null ) {

        $inflectedRow = iterator_to_array( $this->inflectRow( $data ) );

        if( $as === false )
            return $inflectedRow;

        if( $as === null )
            $as = self::DEFAULT_ROW_CLASS_NAME;

        return new $as( $table, $inflectedRow );
    }

    public function countRows( QueryInterface $query, $field = null, $distinct = false ) {

        list( $sql, $args ) = $this->parse( $query );
        $table = $query->getTable();
        $name = $this->quoteName( $table->getDatabase(), $table );

        $countedField = '*';

        if( $field )
            $countedField = "`$field`";

        if( $distinct )
            $countedField = "DISTINCT $countedField";

        $qry = "SELECT COUNT($countedField) FROM $name$sql";

        $stmt = $this->query( $qry, $args );

        return intval( $stmt->fetchColumn( 0 ) );
    }

    public function loadRows( QueryInterface $query, array $fields = null, $as = null ) {

        list( $sql, $args ) = $this->parse( $query );
        $table = $query->getTable();
        $name = $this->quoteName( $table->getDatabase(), $table );

        list( $fields, $index ) = $fields ? $this->parseSelectFields( $fields ) : [ '*', null ];
        $qry = "SELECT $fields FROM $name$sql";

        $stmt = $this->query( $qry, $args );
        $stmt->setFetchMode( \PDO::FETCH_ASSOC );

        while( $row = $stmt->fetch() )
            if( $index ) {

                $indexValue = $row[ $index ];
                yield $indexValue => $this->processRow( $table, $row, $as );
            } else
                yield $this->processRow( $table, $row, $as );
    }

    public function saveRows( QueryInterface $query, array $data ) {

        list( $sql, $args ) = $this->parse( $query );
        $table = $query->getTable();
        $name = $this->quoteName( $table->getDatabase(), $table );

        list( $updateSql, $updateArgs ) = $this->parseData( $data );

        $qry = "UPDATE $name $updateSql$sql";

        $this->query( $qry, ArrayUtils::concat( $updateArgs, $args ) );

        return $this;
    }

    public function createRow( TableInterface $table, array $data ) {

        $name = $this->quoteName( $table->getDatabase(), $table );

        list( $sql, $args ) = $this->parseData( $data );

        $qry = "INSERT INTO $name$sql";

        $this->query( $qry, $args );

        return $this;
    }

    public function removeRows( QueryInterface $query ) {

        list( $sql, $args ) = $this->parse( $query );
        $table = $query->getTable();
        $name = $this->quoteName( $table->getDatabase(), $table );

        $qry = "DELETE FROM $name$sql";

        $this->query( $qry, $args );

        return $this;
    }

    public function getLastId() {

        $stmt = $this->query( 'SELECT LAST_INSERT_ID()' );

        return $stmt->fetchColumn( 0 );
    }
    */
}