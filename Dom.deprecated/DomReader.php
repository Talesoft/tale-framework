<?php

namespace Tale\Dom;

use Tale\Io\StreamReader,
    Tale\Io\StreamInterface,
    Tale\Io\MemoryStream,
    Tale\Io\StreamMode,
    Tale\System\Exception;

class DomReader extends StreamReader {

    private $_options;
    private $_parser;
    private $_currentElement;

    public function __construct( StreamInterface $stream, array $options = null ) {
        parent::__construct( $stream );

        $this->_options = array_replace( [
            'encoding' => 'utf-8',
            'bufferSize' => 8192,
            'elementClassName' => __NAMESPACE__.'\\DomElement'
        ], $options ? $options : [] );

        $this->_parser = xml_parser_create( $this->_options[ 'encoding' ] );

        xml_set_object( $this->_parser, $this );

        xml_parser_set_option( $this->_parser, \XML_OPTION_CASE_FOLDING, false );
        xml_parser_set_option( $this->_parser, \XML_OPTION_SKIP_WHITE, true );

        xml_set_element_handler( $this->_parser, 'readOpenTag', 'readCloseTag' );
        xml_set_character_data_handler( $this->_parser, 'readText' );
    }

    public function __destruct() {

        $this->close();
    }

    public function close() {
        parent::close();

        if( is_resource( $this->_parser ) )
            xml_parser_free( $this->_parser );

        return $this;
    }

    public function getOptions() {

        return $this->_options;
    }

    public function getParser() {

        return $this->_parser;
    }

    public function readElement() {

        $this->_currentElement = null;
        while( $data = $this->read( $this->_options[ 'bufferSize' ] ) )
            if( !xml_parse( $this->_parser, $data, $this->isAtEnd() ) )
                $this->throwException();

        return $this->_currentElement;
    }

    protected function readOpenTag( $parser, $tag, array $attrs ) {

        $type = $this->_options[ 'elementClassName' ];
        $this->_currentElement = new $type( $tag, $attrs, $this->_currentElement );
    }

    protected function readCloseTag( $parser, $tag ) {

        $cur = $this->_currentElement;
        if( !$cur || !( $cur instanceof DomElement ) || ( $cur instanceof DomElement && $cur->getTag() !== $tag ) )
            $this->throwException( "Close-tag mismatch for tag $tag" );

        if( $cur->hasParent() )
            $this->_currentElement = $cur->getParent();
    }

    protected function readText( $pareser, $text ) {

        $text = trim( $text );

        if( empty( $text ) )
            return;

        $this->_currentElement->setText( $text );
    }

    protected function throwException( $message = null ) {

        throw new Exception( 
            sprintf( 
                'Failed to parse DOM: %s on line %d:%d',
                $message ? $message : xml_error_string( xml_get_error_code( $this->_parser ) ),
                xml_get_current_line_number( $this->_parser ),
                xml_get_current_column_number( $this->_parser )
            )
        );
    }
}