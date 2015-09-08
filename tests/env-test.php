<?php

use Tale\ClassLoader;
use Tale\Cli\Option;
use Tale\Cli\Request;

include '../src/Tale/ClassLoader.php';

$loader = new ClassLoader('../src');
$loader->register();


var_dump(\Tale\Environment::getClientOptions());

var_dump(\Tale\Net\Url::fromEnvironment(), (string)\Tale\Net\Url::fromEnvironment());