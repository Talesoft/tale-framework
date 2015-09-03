<?php

namespace Tale\Data;

use Exception;

class Column extends NamedEntityBase
{

    const KEY_PRIMARY = 1;
    const KEY_UNIQUE = 2;
    const KEY_INDEX = 3;

    private static $_types = [
        /* Numeric */
        'bool', 'byte', 'short', 'int', 'long', 'decimal', 'float', 'double',
        /* Text */
        'char', 'string', 'datetime', 'timestamp',
        /* Misc */
        'enum', 'binary'
    ];

    private $_table;
    private $_type;
    private $_maxLength;
    private $_allowedValues;
    private $_autoIncreased;
    private $_optional;
    private $_keyType;
    private $_defaultValue;
    private $_reference;

    public function __construct(Table $table, $name, $load = false, $typeString = null)
    {
        parent::__construct($name);

        $this->_table = $table;
        $this->clear();

        if ($load)
            $this->load();

        if ($typeString)
            $this->parse($typeString);
    }

    protected function clear()
    {

        $this->_type = null;
        $this->_maxLength = null;
        $this->_allowedValues = null;
        $this->_autoIncreased = false;
        $this->_optional = false;
        $this->_keyType = null;
        $this->_defaultValue = null;
        $this->_reference = null;
    }

    public function getTable()
    {

        return $this->_table;
    }

    public function getDatabase()
    {

        return $this->_table->getDatabase();
    }

    public function getSource()
    {

        return $this->_table->getSource();
    }

    public function getType()
    {

        return $this->_type;
    }

    public function setType($type)
    {

        $type = strtolower($type);
        if (!in_array($type, self::$_types))
            throw new Exception("Invalid column type $type encountered, allowed types are: ".implode(', ', self::$_types));

        switch ($type) {
            case 'bool':
            case 'char':
                $this->setMaxLength(1);
                break;
        }

        $this->_type = $type;

        return $this->unsync();
    }

    public function getMaxLength()
    {

        return $this->_maxLength;
    }

    public function setMaxLength($maxLength)
    {

        if (in_array($this->_type, ['bool', 'char']))
            throw new Exception("Failed to set max length for column, bool and char columns are fixed to a max-length of 1");

        $this->_maxLength = is_null($maxLength) ? null : intval($maxLength);

        return $this->unsync();
    }

    public function getAllowedValues()
    {

        return $this->_allowedValues;
    }

    public function setAllowedValues(array $allowedValues)
    {

        $this->_allowedValues = $allowedValues;

        return $this->unsync();
    }

    public function isAutoIncreased()
    {

        return $this->_autoIncreased;
    }

    public function autoIncrease()
    {

        $this->_autoIncreased = true;

        return $this->unsync();
    }

    public function dontAutoIncrease()
    {

        $this->_autoIncreased = false;

        return $this->unsync();
    }

    public function isOptional()
    {

        return $this->_optional;
    }

    public function makeOptional()
    {

        $this->_optional = true;

        return $this->unsync();
    }

    public function makeRequired()
    {

        $this->_optional = false;

        return $this->unsync();
    }

    public function getKeyType()
    {

        return $this->_keyType;
    }

    public function setKeyType($keyType)
    {

        $this->_keyType = $keyType;

        return $this->unsync();
    }

    public function isPrimary()
    {

        return $this->_keyType === self::KEY_PRIMARY;
    }

    public function makePrimary()
    {

        $this->_keyType = self::KEY_PRIMARY;

        return $this->unsync();
    }

    public function isUnique()
    {

        return $this->_keyType === self::KEY_UNIQUE;
    }

    public function makeUnique()
    {

        $this->_keyType = self::KEY_UNIQUE;

        return $this->unsync();
    }

    public function isIndex()
    {

        return $this->_keyType === self::KEY_INDEX;
    }

    public function makeIndex()
    {

        $this->_keyType = self::KEY_INDEX;

        return $this->unsync();
    }

    public function getDefaultValue()
    {

        return $this->_defaultValue;
    }

    public function setDefaultValue($defaultValue)
    {

        $this->_defaultValue = $defaultValue;

        return $this->unsync();
    }

    public function equals(Column $otherColumn, $namesOnly = true)
    {

        if (($this->_table->getName() !== $otherColumn->getTable()->getName())
            || ($this->getName() !== $otherColumn->getName())
        )
            return false;

        if ($namesOnly)
            return true;

        $otherRef = $otherColumn->getReference();
        if (($this->_type !== $otherColumn->getType())
            || ($this->_maxLength && $this->_maxLength !== $otherColumn->getMaxLength())
            || ($this->_allowedValues != $otherColumn->getAllowedValues())
            || ($this->_keyType !== $otherColumn->getKeyType())
            || ($this->_autoIncreased !== $otherColumn->isAutoIncreased())
            || ($this->_optional !== $otherColumn->isOptional())
            || ($this->_defaultValue !== $otherColumn->getDefaultValue())
            || ($this->_reference && !$otherRef)
            || (!$this->_reference && $otherRef)
            || ($this->_reference && $otherRef && !$this->_reference->equals($otherRef))
        )
            return false;

        return true;
    }

    public function belongsTo(Table $table)
    {

        return $this->_table->equals($table);
    }

    public function getReference()
    {

        return $this->_reference;
    }

    public function reference(Column $otherColumn)
    {

        $this->makeIndex();
        $this->_reference = $otherColumn;

        return $this;
    }

    public function dereference()
    {

        if (!$this->_reference)
            return $this;

        $this->setKeyType(null);
        $this->_reference = null;

        return $this;
    }

    public function parse($typeString)
    {

        $parts = explode(' ', $typeString);

        $this->clear();

        foreach ($parts as $part) {

            switch ($part) {
                case 'autoIncrement':
                case 'autoIncrease':

                    $this->autoIncrease();
                    break;
                case 'allowNull':
                case 'null':
                case 'optional':

                    $this->makeOptional();
                    break;
                case 'disallowNull':
                case 'notNull':
                case 'required':

                    $this->makeRequired();
                    break;
                case 'unique':

                    $this->makeUnique();
                    break;
                case 'primary':

                    $this->makePrimary();
                    break;

                /* Custom Types! */
                case 'id':

                    $this->parse('int(11) required primary autoIncrease');
                    break;
                case 'fk':

                    $this->parse('int(11) required index');
                    break;

                default:

                    $matches = [];
                    if (!preg_match('/^(?<type>[a-zA-Z]+)(?:\((?<extra>[^\)]+)\))?$/i', $part, $matches))
                        throw new Exception("Unexpected type string token $part");

                    $type = $matches['type'];
                    $extra = isset($matches['extra']) ? $matches['extra'] : null;

                    if ($type === 'default') {

                        $this->setDefaultValue($extra);
                        break;
                    }

                    if ($type === 'reference' || $type === 'references') {

                        if (!$extra)
                            throw new \Exception("Failed to parse type string: no value for reference specified");

                        $parts = explode('.', $extra);

                        $tblName = $parts[0];
                        $tbl = $this->getDatabase()->{$tblName};

                        $col = null;
                        if (count($parts) > 1) {

                            $colName = $parts[1];
                            $col = $tbl->{$colName};
                        }

                        if (!$col)
                            $col = $tbl->getPrimaryColumn();

                        if (!$col)
                            throw new \Exception("Failed to reference $tbl: No primary column found or specified");

                        $this->reference($col);
                        break;
                    }

                    if ($extra)
                        if (is_numeric($extra))
                            $this->setMaxLength(intval($extra));
                        else
                            $this->setAllowedValues(explode(',', $extra));

                    $this->setType($type);
            }
        }

        return $this;
    }

    public function exists()
    {

        return $this->getSource()->hasColumn($this);
    }

    public function load()
    {

        $this->clear();
        $this->getSource()->loadColumn($this);

        return $this->sync();
    }

    public function save()
    {

        $this->getSource()->saveColumn($this);

        return $this->sync();
    }

    public function create(array $data = null)
    {

        $this->getSource()->createColumn($this);

        return $this->sync();
    }

    public function remove()
    {

        $this->getSource()->removeColumn($this);

        return $this->unsync();
    }

    public static function getTypes()
    {

        return self::$_types;
    }
}