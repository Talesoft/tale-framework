<?php

namespace Tale\Io\Text;

use Tale\Io\StreamWriter;

class TextWriter extends StreamWriter {

    public function writeText( $text ) {

        return $this->write( $text, strlen( $text ) );
    }

    public function writeLine( $text ) {

        return $this->writeText( $text."\n" );
    }

    public function writeLines( array $lines ) {

        foreach( $lines as $line )
            $this->writeLine( $line );

        return $this;
    }
}