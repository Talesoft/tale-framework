<?php

namespace Tale\Theme\Preprocessor\Phtml;

use Tale\Jade\Compiler;
use Tale\Jade\Parser;
use Tale\Theme\PreprocessorBase;

class Jade extends PreprocessorBase {

    public function process( $inputPath, $outputPath ) {

        $parser = new Parser( file_get_contents( $inputPath ), [
            'filename' => $inputPath,
            'includes' => [ dirname( $inputPath ) ]
        ] );
        $compiler = new Compiler( !$this->getManager()->getConfig()->minify );
        file_put_contents( $outputPath, $compiler->compile( $parser->parse() ) );
    }
}