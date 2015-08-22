<?php

namespace Tale\Debug\Dumper;

use Tale\Debug\DumperBase,
    Tale\Dom\Html\Manipulator;

class Html extends Text {

    protected function dumpArray( array $value ) {

        $b = 255 - min( 10 + 10 * $this->getLevel(), 255 );
        $bb = 255 - min( 30 + 10 * $this->getLevel(), 255 );
        $tb = 255 - min( 100 + 10 * $this->getLevel(), 255 );
        $color = "rgb( $b, $b, 255 )";
        $borderColor = "rgb( $bb, $bb, 255 )";
        $textColor = "rgb( $tb, $tb, 255 )";

        $div = new Manipulator( 'div' );
        $length = count( $value );

        $table = $div->div
                        ->strong
                            ->setText( "Array" )
                            ->parent
                        ->small
                            ->setText( "($length Items)" )
                            ->parent
                        ->parent
                     ->table;


        $table->tableCols( [ 'Key', 'Value' ] );

        foreach( $value as $key => $val ) {

            $table->tableRows( [ [ $this->dumpMixed( $key ), $this->dumpMixed( $val ) ] ] );
        }

        $div->setCss( [
            'border' => "1px solid $borderColor",
            'background' => $color,
            'padding' => '10px',
            'font-family' => 'monospace',
            'font-size' => '14px',
            'display' => 'inline-block',
            'vertical-align' => 'top'
        ] );

        $div->find( 'strong, th' )->setCss( [ 'color' => $textColor ] );

        $div->table->setCss( [ 'border-collapse' => 'collapse', 'width' => '100%' ] );

        $div->find( 'td' )->setCss( [ 'vertical-align' => 'top' ] );
        $div->find( 'td, th' )->setCss( [ 'border' => "1px solid $borderColor", 'padding' => '3px' ] );


        return $div->getString();
    }

    protected function dumpObject( $value ) {

        $length = count( $value );

        $g = 255 - min( 10 + 10 * $this->getLevel(), 255 );
        $bg = 255 - min( 120 + 10 * $this->getLevel(), 255 );
        $tg = 255 - min( 160 + 10 * $this->getLevel(), 255 );
        $color = "rgb( $g, 255, $g )";
        $borderColor = "rgb( $bg, 120, $bg )";
        $textColor = "rgb( $tg, 160, $tg )";

        $div = new Manipulator( 'div' );

        $table = $div->div
                    ->strong
                        ->setText( "Object" )
                        ->parent
                    ->small
                        ->setText( '(Class: '.get_class( $value ).')' )
                        ->parent
                    ->parent
                ->table;


        $table->tableCols( [ 'Property', 'Value' ] );

        $ref = new \ReflectionClass( get_class( $value ) );

        $classes = [ $ref ];
        $parent = $ref;
        $firstParent = null;
        while( $parent = $parent->getParentClass() ) {

            if( !$firstParent )
                $firstParent = $parent;

            $classes[] = $parent;
        }

        $tbody = $table->tbody;
        foreach( $classes as $classRef ) {

            foreach( $classRef->getProperties() as $prop ) {

                $tr = $table->append( 'tr' );

                $prop->setAccessible( true );

                $tr->append( 'td' )
                    ->append( 'small' )
                        ->setText( $prop->isStatic() ? '::' : '->' )
                        ->parent
                    ->append( 'strong' )
                        ->setText( $prop->getName() )
                        ->parent
                    ->append( 'small' )
                        ->setText( '('.$classRef->getName().')' )
                        ->parent
                    ->parent
                   ->append( 'td' )
                    ->setText( $this->dumpMixed( $prop->getValue( $value ) ) );
            }
        }

        $div->setCss( [
            'border' => "1px solid $borderColor",
            'background' => $color,
            'padding' => '10px',
            'font-family' => 'monospace',
            'font-size' => '14px',
            'display' => 'inline-block',
            'vertical-align' => 'top'
        ] );

        $div->find( 'strong, th' )->setCss( [ 'color' => $textColor ] );

        $div->table->setCss( [ 'border-collapse' => 'collapse', 'width' => '100%' ] );

        $div->find( 'td' )->setCss( [ 'vertical-align' => 'top' ] );
        $div->find( 'td, th' )->setCss( [ 'border' => "1px solid $borderColor", 'padding' => '3px' ] );

        return $div->getString();
    }

    protected function dumpTooDeep( $value ) {

        return '**TOO DEEP**';
    }

    protected function dumpRecursion( $value ) {

        return '**RECURSION**';
    }

    protected function dumpString( $value ) {

        $div = new Manipulator( 'div' );

        $strong = $div->strong;
        $strong->setCss( [
            'color' => 'rgb( 88, 0, 0 )',
            'font-family' => 'monospace',
            'font-size' => '14px'
        ] );
        $strong->setText( "\"$value\"" );

        $length = $div->small;
        $length->setCss( [
             'color' => 'rgb( 88, 88, 88 )',
             'font-family' => 'monospace',
             'font-size' => '10px'
         ] );
        $length->setText( '('.strlen( $value ).')' );

        return $div->getString();
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