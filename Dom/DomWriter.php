<?php

namespace Tale\Dom;

use Tale\Io\Text\TextWriter,
    Tale\Io\StreamInterface;

class DomWriter extends TextWriter {

    private $_pretty;
    private $_level;
    private $_space;
    private $_newLine;
    private $_tabWidth;
    private $_tabString;
    private $_textWrap;
    private $_selfClosingTags;
    private $_selfClosingString;

    public function __construct( StreamInterface $stream, array $options = null ) {
        parent::__construct( $stream );

        $options = array_replace( [
            'pretty' => false,
            'level' => 0,
            'newLine' => "\n",
            'space' => ' ',
            'tabWidth' => 4,
            'tabString' => null,
            'textWrap' => 60,
            'selfClosingTags' => [],
            'selfClosingString' => ' /'
        ], $options ? $options : [] );

        $this->_pretty = $options[ 'pretty' ];
        $this->_space = $options[ 'space' ];
        $this->_newLine = $options[ 'newLine' ];
        $this->_level = $options[ 'level' ];
        $this->_tabWidth = $options[ 'tabWidth' ];
        $this->_tabString = $options[ 'tabString' ] 
                          ? str_pad( $options[ 'tabString' ], $this->_tabWidth, $this->_space, \STR_PAD_BOTH )
                          : str_repeat( $this->_space, $this->_tabWidth );
        $this->_textWrap = $options[ 'textWrap' ];
        $this->_selfClosingTags = $options[ 'selfClosingTags' ];
        $this->_selfClosingString = $options[ 'selfClosingString' ];
    }

    public function isPretty() {

        return $this->_pretty;
    }

    public function getLevel() {

        return $this->_level;
    }

    public function getSpace() {

        return $this->_space;
    }

    public function getNewLine() {

        return $this->_newLine;
    }

    public function getTabWidth() {

        return $this->_tabWidth;
    }

    public function getTabString() {

        return $this->_tabString;
    }

    public function getTextWrap() {

        return $this->_textWrap;
    }

    public function getSelfClosingTags() {

        return $this->_selfClosingTags;
    }

    public function getSelfClosingString() {

        return $this->_selfClosingString;
    }

    public function writeElement( DomElement $element, $pretty = null ) {

        if( !is_null( $pretty ) )
            $this->_pretty = $pretty;

        $this->writeLeaf( $element );

        return $this;
    }

    protected function writeDomText( DomText $textChild, $level ) {

        $newLine = $this->_pretty ? $this->_newLine : '';
        $indent = $this->_pretty ? str_repeat( $this->_tabString, $level ) : '';
        $text = $textChild->getText();
            
        $this->writeText( 
            $this->_pretty && strlen( $text ) > $this->_textWrap 
          ? $indent.wordwrap( str_replace( "\n", '', $text ), $this->_textWrap, "$newLine$indent" ) 
          : $text 
        );
    }

    protected function writeAttributes( DomAttributeSet $attributes ) {

        $attrString = (string)$attributes;

        if( !empty( $attrString ) )
            $this->writeText( " $attrString" );
    }

    protected function writeOpenTag( $tag, DomAttributeSet $attributes, $hasChildren, $level ) {

        $indent = $this->_pretty ? str_repeat( $this->_tabString, $level ) : '';

        $this->writeText( "<$tag" );
        $this->writeAttributes( $attributes );

        if( !$hasChildren && ( empty( $this->_selfClosingTags ) || in_array( $tag, $this->_selfClosingTags ) ) )
            $this->writeText( $this->_selfClosingString );

        $this->writeText( '>' );
    }

    protected function writeCloseTag( $tag ) {

        $this->writeText( "</$tag>" );
    }

    protected function writeDomElement( DomElement $element, $level ) {

        $children = $element->getChildren();
        $childCount = count( $children );
        $hasChildren = $childCount > 0;

        $newLine = $this->_pretty ? $this->_newLine : '';
        $indent = $this->_pretty ? str_repeat( $this->_tabString, $level ) : '';

        $this->writeText( $indent );
        $this->writeOpenTag( $element->getTag(), $element->getAttributes(), $hasChildren, $level );

        $writeCloseIndent = false;

        if( $hasChildren ) {

            if( $childCount === 1 && $children[ 0 ] instanceof DomText && strlen( $children[ 0 ]->getText() ) < $this->_textWrap ) {

                $this->writeText( $children[ 0 ]->getText(), $level + 1 );
            } else {

                $this->writeText( $newLine );

                for( $i = 0; $i < $childCount; $i++ ) {

                    $child = $children[ $i ];

                    $this->writeLeaf( $child, $level + 1 );
                    $this->writeText( $newLine );
                }

                $writeCloseIndent = true;
            }
        }

        if( $hasChildren || ( !empty( $this->_selfClosingTags ) && !in_array( $element->getTag(), $this->_selfClosingTags ) ) ) {

            if( $hasChildren && $writeCloseIndent )
                $this->writeText( $indent );

            $this->writeCloseTag( $element->getTag() );
        }
    }

    protected function writeLeaf( DomLeaf $child, $level = null ) {

        $level = $level ? $level : $this->_level;

        $newLine = $this->_pretty ? $this->_newLine : '';
        $indent = $this->_pretty ? str_repeat( $this->_tabString, $level ) : '';

        if( $child instanceof DomText )
            return $this->writeDomText( $child, $level );

        if( $child instanceof DomElement )
            return $this->writeDomElement( $child, $level );
    }
}