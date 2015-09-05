<?php

use Tale\ClassLoader;
use Tale\Cli\Option;
use Tale\Cli\Request;

include '../src/Tale/ClassLoader.php';

$loader = new ClassLoader('../src');
$loader->register();


$request = new Request([
    new Option('data-source', null, 'The data source', true),
    new Option('controller', 'c', 'The controller name', false),
    new Option('action', 'a', 'The action name', false),
    new Option('id', null, 'The id', false),
    new Option('json', null, 'Result in JSON?'),
    new Option('xml', null, 'Result in XML?')
]);

echo $request->getOptionText();

var_dump($request->getOptionValues());

$request->validate();