<?php

namespace Tale\Debug\Dumper;

use Tale\Debug\DumperBase;

class Text extends DumperBase {

    protected function dumpArray( array $value ) {


        $preIndent = str_repeat( "  ", $this->getLevel() - 1 );
        $indent = str_repeat( "  ", $this->getLevel() );

        $length = count( $value );
        $str = "Array($length)[\n";
        foreach( $value as $key => $val ) {
            $str .= "{$indent}[".$this->dumpMixed( $key )."] => ".$this->dumpMixed( $val )."\n";
        }
        $str .= "$preIndent]";

        return $str;
    }

    protected function dumpObject( $value ) {

        $preIndent = str_repeat( "  ", $this->getLevel() - 1 );
        $indent = str_repeat( "  ", $this->getLevel() );

        $length = count( $value );


        $ref = new \ReflectionClass( get_class( $value ) );

        $classes = [ $ref ];
        $parent = $ref;
        $firstParent = null;
        while( $parent = $parent->getParentClass() ) {

            if( !$firstParent )
                $firstParent = $parent;

            $classes[] = $parent;
        }

        $str = "Object(".$ref->getName();

        if( $firstParent )
            $str .= ':'.$firstParent->getName();

        $str .= "){\n";

        foreach( $classes as $classRef ) {

            foreach( $classRef->getProperties() as $prop ) {

                $prop->setAccessible( true );

                $str .= $indent.( $prop->isStatic() ? '::' : '->' ).$prop->getName().'('.$classRef->getName().')'.' = '.$this->dumpMixed( $prop->getValue( $value ) )."\n";
            }
        }

        $str .= "$preIndent}";

        return $str;
    }

    protected function dumpTooDeep( $value ) {

        return '**TOO DEEP**';
    }

    protected function dumpRecursion( $value ) {

        return '**RECURSION**';
    }

    protected function dumpString( $value ) {

        return "\"$value\"";
    }

    protected function dumpInteger( $value ) {

        return strval( $value );
    }

    protected function dumpFloat( $value ) {

        return strval( $value );
    }

    protected function dumpBoolean( $value ) {

        return $value ? 'true' : 'false';
    }

    protected function dumpResource( $value ) {

        return 'Resource('.get_resource_type( $value ).')#'.intval( $value );
    }

    protected function dumpNull( $value ) {

        return 'null';
    }
}