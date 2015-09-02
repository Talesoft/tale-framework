<?php

namespace Tale\Dom;

class Writer {

    private $_pretty;
    private $_level;
    private $_space;
    private $_newLine;
    private $_tabWidth;
    private $_tabString;
    private $_textWrap;
    private $_selfClosingTags;
    private $_selfClosingString;

    public function __construct( array $options = null ) {

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

    public function writeLeaf( Leaf $child, $level = null ) {

        $level = $level ? $level : $this->_level;

        if( $child instanceof Text )
            return $this->writeText( $child, $level );

        if( $child instanceof Element )
            return $this->writeElement( $child, $level );

        return '';
    }

    protected function writeText( Text $textChild, $level ) {

        $newLine = $this->_pretty ? $this->_newLine : '';
        $indent = $this->_pretty ? str_repeat( $this->_tabString, $level ) : '';
        $text = $textChild->getText();

        //TODO: Need some kind of mb_wordwrap here, maybe:
        //http://stackoverflow.com/questions/3825226/multi-byte-safe-wordwrap-function-for-utf-8?
        return $this->_pretty && mb_strlen( $text, 'utf-8' ) > $this->_textWrap
             ? $indent.wordwrap( str_replace( "\n", '', $text ), $this->_textWrap, "$newLine$indent" )
             : $text;
    }

    protected function writeAttributes( array $attributes ) {

        $str = '';
        foreach( $attributes as $key => $val )
            $str .= " $key=\"$val\"";

        return $str;
    }

    protected function writeOpenTag( $tag, array $attributes, $hasChildren ) {

        $str = "<$tag";
        $str .= $this->writeAttributes( $attributes );

        if( !$hasChildren && ( empty( $this->_selfClosingTags ) || in_array( $tag, $this->_selfClosingTags ) ) )
            $str .= $this->_selfClosingString;

        return $str.'>';
    }

    protected function writeCloseTag( $tag ) {

        return "</$tag>";
    }

    protected function writeElement( Element $element, $level ) {

        $children = $element->getChildren();
        $childCount = count( $children );
        $hasChildren = $childCount > 0;

        $newLine = $this->_pretty ? $this->_newLine : '';
        $indent = $this->_pretty ? str_repeat( $this->_tabString, $level ) : '';

        $str = $indent;
        $str .= $this->writeOpenTag( $element->getTag(), $element->getAttributes(), $hasChildren );

        $writeCloseIndent = false;
        if( $hasChildren ) {

            if( $childCount === 1 && $children[ 0 ] instanceof Text && mb_strlen( $children[ 0 ]->getText(), 'utf-8' ) < $this->_textWrap ) {

                $str .= $children[ 0 ]->getText();
            } else {

                $str .= $newLine;

                for( $i = 0; $i < $childCount; $i++ ) {

                    $child = $children[ $i ];

                    $str .= $this->writeLeaf( $child, $level + 1 );
                    $str .= $newLine;
                }

                $writeCloseIndent = true;
            }
        }

        if( $hasChildren || ( !empty( $this->_selfClosingTags ) && !in_array( $element->getTag(), $this->_selfClosingTags ) ) ) {

            if( $hasChildren && $writeCloseIndent )
                $str .= $indent;

            $str .= $this->writeCloseTag( $element->getTag() );
        }

        return $str;
    }
}