<?php

namespace Tale\Crud;

use Tale\Crud\Type\BoolType;
use Tale\Crud\Type\DoubleType;
use Tale\Crud\Type\IntType;
use Tale\Crud\Type\StringType;
use Tale\Factory;

class Type
{

    /** @var  \Tale\Factory */
    private static $_typeFactory;

    public static function getInbuiltTypes()
    {

        return [
            'bool' => 'Tale\\Crud\\Type\\BoolType',
            'boolean' => 'Tale\\Crud\\Type\\BoolType',
            'byte' => 'Tale\\Crud\\Type\\ByteType',
            'ubyte' => 'Tale\\Crud\\Type\\UByteType',
            'short' => 'Tale\\Crud\\Type\\ShortType',
            'ushort' => 'Tale\\Crud\\Type\\UShortType',
            'int' => 'Tale\\Crud\\Type\\IntType',
            'integer' => 'Tale\\Crud\\Type\\IntType',
            'uint' => 'Tale\\Crud\\Type\\UIntType',
            'long' => 'Tale\\Crud\\Type\\LongType',
            'ulong' => 'Tale\\Crud\\Type\\ULongType',
            'double' => 'Tale\\Crud\\Type\\DoubleType',
            'char' => 'Tale\\Crud\\Type\\CharType',
            'string' => 'Tale\\Crud\\Type\\StringType',
            'datetime' => 'Tale\\Crud\\Type\\DateTimeType',
            'timestamp' => 'Tale\\Crud\\Type\\TimeStampType',
            'enum' => 'Tale\\Crud\\Type\\EnumType',
            'binary' => 'Tale\\Crud\\Type\\BinaryType',
            'array' => 'Tale\\Crud\\Type\\ArrayType',
            'object' => 'Tale\\Crud\\Type\\ObjectType'
        ];
    }

    public static function getTypeFactory()
    {

        if (!isset(self::$_typeFactory))
            self::$_typeFactory = new Factory('Tale\\Crud\\TypeBase', self::getInbuiltTypes() );

        return self::$_typeFactory;
    }

    public function create($className, $value)
    {

        $args = func_get_args();
        array_shift($args);

        return self::$_typeFactory->createInstance($className, $args);
    }

    public static function convert($value)
    {

        switch (gettype($value)) {
            case 'boolean': return new BoolType($value); break;
            case 'integer': return new IntType($value); break;
            case 'double': return new DoubleType($value); break;
            case 'string': return new StringType($value); break;
            case 'array': return new ArrayType($value); break;
            case 'object': return new ObjectType($value); break;
            case 'resource':
            case 'NULL':
                return null;
        }
    }
}