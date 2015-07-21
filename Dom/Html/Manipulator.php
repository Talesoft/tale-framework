<?php

namespace Tale\Dom\Html;

use Tale\Dom\Manipulator as DomManipulator,
    Exception;

class Manipulator extends DomManipulator {

    public function headLine( $text, $size = 1, $selector = null ) {

        return $this->addOrAppend( $selector ? "h$size$selector" : "h$size" )
                    ->setText( $text );
    }

    public function content( $text, $align = null, $selector = null ) {

        $p = $this->addOrAppend( $selector ? "p$selector" : 'p' )
                  ->setText( $text );

        if( $align )
            $p->setCss( [ 'text-align' => $align ] );

        return $p;
    }

    public function tableCols( array $columns, $head = true, $foot = false ) {

        if( !$this->is( 'table' ) )
            throw new Exception( "Table cols has to be used on a table-element" );

        $els = new self();
        if( $head ) {

            $thead = $this->find( '>thead' );
            if( !count( $thead ) )
                $thead = $this->append( 'thead' );

            $els = $els->add( $thead );
        }

        if( $foot ) {

            $tfoot = $this->find( '>tfoot' );
            if( !count( $tfoot ) )
                $tfoot = $this->append( 'tfoot' );

            $els = $els->add( $tfoot );
        }

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