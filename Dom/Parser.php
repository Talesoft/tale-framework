<?php

namespace Tale\Dom;

use Exception;

class Parser {

    private $_options;
    private $_parser;
    private $_currentElement;

    public function __construct( array $options = null ) {

        $this->_options = array_replace( [
            'encoding' => 'utf-8'
        ], $options ? $options : [] );
    }

    public function getOptions() {

        return $this->_options;
    }

    private function _createParser() {

        $this->_parser = xml_parser_create( $this->_options[ 'encoding' ] );

        xml_set_object( $this->_parser, $this );

        xml_parser_set_option( $this->_parser, \XML_OPTION_CASE_FOLDING, false );
        xml_parser_set_option( $this->_parser, \XML_OPTION_SKIP_WHITE, true );

        xml_set_element_handler( $this->_parser, 'readOpenTag', 'readCloseTag' );
        xml_set_character_data_handler( $this->_parser, 'readText' );
    }

    private function _freeParser() {

        if( is_resource( $this->_parser ) )
            xml_parser_free( $this->_parser );

        $this->_parser = null;
    }

    public function parse( $string ) {

        $this->_createParser();
        $this->_currentElement = null;
        if( !xml_parse( $this->_parser, $string ) )
            $this->throwException();
        $this->_freeParser();

        return $this->_currentElement;
    }

    protected function readOpenTag( $parser, $tag, array $attrs ) {

        $type = static::getElementClassName();
        $this->_currentElement = new $type( $tag, $attrs, $this->_currentElement );
    }

    protected function readCloseTag( $parser, $tag ) {

        $cur = $this->_currentElement;
        if( !$cur || !( $cur instanceof Element ) || ( $cur instanceof Element && $cur->getTag() !== $tag ) )
            $this->throwException( "Close-tag mismatch for tag $tag" );

        if( $cur->hasParent() )
            $this->_currentElement = $cur->getParent();
    }

    protected function readText( $parser, $text ) {

        $text = trim( $text );

        if( empty( $text ) )
            return;

        $this->_currentElement->setText( $text );
    }

    protected function throwException( $message = null ) {

        $this->_freeParser();
        throw new Exception( 
            sprintf( 
                'Failed to parse DOM: %s on line %d:%d',
                $message ? $message : xml_error_string( xml_get_error_code( $this->_parser ) ),
                xml_get_current_line_number( $this->_parser ),
                xml_get_current_column_number( $this->_parser )
            )
        );
    }

    public static function getElementClassName() {

        return __NAMESPACE__.'\\Element';
    }
}