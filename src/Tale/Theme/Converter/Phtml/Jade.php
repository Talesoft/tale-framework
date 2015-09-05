<?php

namespace Tale\Theme\Converter\Phtml;

use Tale\Jade\Compiler;
use Tale\Jade\Parser;
use Tale\Theme\ConverterBase;

class Jade extends ConverterBase
{

    public function convert($inputPath)
    {

        $this->prependOptions([
           'paths' => []
        ]);

        $parser = new Parser(file_get_contents($inputPath), [
            'filename' => $inputPath,
            'includes' => $this->getOption('paths')
        ]);
        $compiler = new Compiler(!$this->getManager()->getOption('minify'));

        return $compiler->compile($parser->parse());
    }
}