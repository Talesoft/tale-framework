<?php

namespace Tale;

class Debug {

    private static $_dumperFactory;

    private function __construct() {}

    public static function getDumperFactory() {

        if( isset( self::$_dumperFactory ) )
            return self::$_dumperFactory;

        self::$_dumperFactory = new Factory( 'Tale\\Debug\\DumperBase', [
            'text' => 'Tale\\Debug\\Dumper\\Text',
            'html' => 'Tale\\Debug\\Dumper\\Html'
        ] );

        return self::$_dumperFactory;
    }

    public static function dump( $value, $type = null, array $options = null ) {

        $type = $type ? $type : 'text';

        $dumper = self::getDumperFactory()->createInstance( $type, [ $options ] );
        return $dumper->dump( $value );
    }
}