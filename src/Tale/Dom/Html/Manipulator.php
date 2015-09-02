<?php

namespace Tale\Dom\Html;

use Tale\Dom\Manipulator as DomManipulator,
    Exception;

class Manipulator extends DomManipulator {

    public function html( $lang = null, $documentType = null ) {

        $lang = $lang ? $lang : 'en';

        $doc = new Document( $documentType, [ 'lang' => $lang ] );

        return $this->appendOrAdd( $doc );
    }

    public function headLine( $text, $size = 1, $selector = null ) {

        return $this->appendOrAdd( $selector ? "h$size$selector" : "h$size" )
                    ->setText( $text );
    }

    public function content( $text, $align = null, $selector = null ) {

        $p = $this->appendOrAdd( $selector ? "p$selector" : 'p' )
                  ->setText( $text );

        if( $align )
            $p->setCss( [ 'text-align' => $align ] );

        return $p;
    }

    public function tableCols( array $columns, $head = true, $foot = false ) {

        if( !$this->is( 'table' ) )
            throw new Exception( "Table cols has to be used on a table-element" );

        $els = new self();
        if( $head )
            $els = $els->add( $this->thead );

        if( $foot )
            $els = $els->add( $this->tfoot );

        $tr = $els->append( 'tr' );

        foreach( $columns as $name => $selector ) {

            if( is_int( $name ) ) {

                $tr->append( 'th' )
                   ->setText( $selector );
            } else {

                $tr->append( $selector )
                   ->setText( $name );
            }
        }

        return $this;
    }

    public function tableRows( array $rows ) {

        if( !$this->is( 'table' ) )
            throw new Exception( "Table rows has to be used on a table-element" );

        $tbody = $this->find( '>tbody' );
        if( !count( $tbody ) )
            $tbody = $this->append( 'tbody' );

        foreach( $rows as $i => $row ) {

            $tr = $tbody->append( 'tr' );
            foreach( $row as $cell ) {

                $tr->append( 'td' )
                   ->setText( $cell );
            }
        }

        return $this;
    }

    public static function getElementClassName() {

        return __NAMESPACE__.'\\Element';
    }
}