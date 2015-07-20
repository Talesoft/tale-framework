<?php

namespace Tale\Io\Text;

use Tale\Io\StreamReader;

class TextReader extends StreamReader {

    public function readChar() {

        return (string)$this->read( 1 );
    }

    public function readText( $bufferSize = 1024 ) {

        $text = '';
        while( !$this->isAtEnd() )
            $text .= $this->read( $bufferSize );

        return $text;
    }

    public function readLine() {

        if( $this->isAtEnd() )
            return null;

        $line = '';
        while( !$this->isAtEnd() && ( ( $c = $this->readChar() ) !== "\n" ) )
            $line .= $c;

        return $line;
    }

    public function readLines() {

        while( !$this->isAtEnd() && !is_null( $line = $this->readLine() ) )
            yield $line;
    }

    public function readLineArray() {

        return iterator_to_array( $this->readLines() );
    }
}