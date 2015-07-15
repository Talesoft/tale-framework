<?php

namespace Tale;

use Exception,
    InvalidArgumentException;

class StringUtils {

    private static $_uncountables = [
        'equipment', 
        'information', 
        'rice', 
        'money', 
        'species', 
        'series', 
        'fish', 
        'sheep'
    ];

    private static $_irregulars = [
        'person' => 'people',
        'man' => 'men',
        'child' => 'children',
        'sex' => 'sexes',
        'move' => 'moves'
    ];

    private static $_plurals = [
        '/(quiz)$/i' => '$1zes',
        '/^(ox)$/i' => '$1en',
        '/([m|l])ouse$/i' => '$1ice',
        '/(matr|vert|ind)ix|ex$/i' => '$1ices',
        '/(x|ch|ss|sh)$/i' => '$1es',
        '/([^aeiouy]|qu)ies$/i' => '$1y',
        '/([^aeiouy]|qu)y$/i' => '$1ies',
        '/(hive)$/i' => '$1s',
        '/(?:([^f])fe|([lr])f)$/i' => '$1$2ves',
        '/sis$/i' => 'ses',
        '/([ti])um$/i' => '$1a',
        '/(buffal|tomat)o$/i' => '$1oes',
        '/(bu)s$/i' => '$1ses',
        '/(alias|status)/i'=> '$1es',
        '/(octop|vir)us$/i'=> '$1i',
        '/(ax|test)is$/i'=> '$1es',
        '/s$/i'=> 's',
        '/$/'=> 's'
    ];

    private static $_singulars = [
        '/(quiz)zes$/i' => '\1',
        '/(matr)ices$/i' => '\1ix',
        '/(vert|ind)ices$/i' => '\1ex',
        '/^(ox)en/i' => '\1',
        '/(alias|status)es$/i' => '\1',
        '/([octop|vir])i$/i' => '\1us',
        '/(cris|ax|test)es$/i' => '\1is',
        '/(shoe)s$/i' => '\1',
        '/(o)es$/i' => '\1',
        '/(bus)es$/i' => '\1',
        '/([m|l])ice$/i' => '\1ouse',
        '/(x|ch|ss|sh)es$/i' => '\1',
        '/(m)ovies$/i' => '\1ovie',
        '/(s)eries$/i' => '\1eries',
        '/([^aeiouy]|qu)ies$/i' => '\1y',
        '/([lr])ves$/i' => '\1f',
        '/(tive)s$/i' => '\1',
        '/(hive)s$/i' => '\1',
        '/([^f])ves$/i' => '\1fe',
        '/(^analy)ses$/i' => '\1sis',
        '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
        '/([ti])a$/i' => '\1um',
        '/(n)ews$/i' => '\1ews',
        '/s$/i' => '',
    ];

    private static $_stopWords = [
        'a',
        'about',
        'above',
        'across',
        'after',
        'afterwards',
        'again',
        'against',
        'all',
        'almost',
        'alone',
        'along',
        'already',
        'also',
        'although',
        'always',
        'am',
        'among',
        'amongst',
        'amoungst',
        'amount',
        'an',
        'and',
        'another',
        'any',
        'anyhow',
        'anyone',
        'anything',
        'anyway',
        'anywhere',
        'are',
        'around',
        'as',
        'at',
        'back',
        'be',
        'became',
        'because',
        'become',
        'becomes',
        'becoming',
        'been',
        'before',
        'beforehand',
        'behind',
        'being',
        'below',
        'beside',
        'besides',
        'between',
        'beyond',
        'bill',
        'both',
        'bottom',
        'but',
        'by',
        'call',
        'can',
        'cannot',
        'cant',
        'co',
        'computer',
        'con',
        'could',
        'couldnt',
        'cry',
        'de',
        'describe',
        'detail',
        'do',
        'done',
        'down',
        'due',
        'during',
        'each',
        'eg',
        'eight',
        'either',
        'eleven',
        'else',
        'elsewhere',
        'empty',
        'enough',
        'etc',
        'even',
        'ever',
        'every',
        'everyone',
        'everything',
        'everywhere',
        'except',
        'few',
        'fifteen',
        'fify',
        'fill',
        'find',
        'fire',
        'first',
        'five',
        'for',
        'former',
        'formerly',
        'forty',
        'found',
        'four',
        'from',
        'front',
        'full',
        'further',
        'get',
        'give',
        'go',
        'had',
        'has',
        'hasnt',
        'have',
        'he',
        'hence',
        'her',
        'here',
        'hereafter',
        'hereby',
        'herein',
        'hereupon',
        'hers',
        'herse"',
        'him',
        'himse"',
        'his',
        'how',
        'however',
        'hundred',
        'i',
        'ie',
        'if',
        'in',
        'inc',
        'indeed',
        'interest',
        'into',
        'is',
        'it',
        'its',
        'itse"',
        'keep',
        'last',
        'latter',
        'latterly',
        'least',
        'less',
        'ltd',
        'made',
        'many',
        'may',
        'me',
        'meanwhile',
        'might',
        'mill',
        'mine',
        'more',
        'moreover',
        'most',
        'mostly',
        'move',
        'much',
        'must',
        'my',
        'myse"',
        'name',
        'namely',
        'neither',
        'never',
        'nevertheless',
        'next',
        'nine',
        'no',
        'nobody',
        'none',
        'noone',
        'nor',
        'not',
        'nothing',
        'now',
        'nowhere',
        'of',
        'off',
        'often',
        'on',
        'once',
        'one',
        'only',
        'onto',
        'or',
        'other',
        'others',
        'otherwise',
        'our',
        'ours',
        'ourselves',
        'out',
        'over',
        'own',
        'part',
        'per',
        'perhaps',
        'please',
        'put',
        'rather',
        're',
        'same',
        'see',
        'seem',
        'seemed',
        'seeming',
        'seems',
        'serious',
        'several',
        'she',
        'should',
        'show',
        'side',
        'since',
        'sincere',
        'six',
        'sixty',
        'so',
        'some',
        'somehow',
        'someone',
        'something',
        'sometime',
        'sometimes',
        'somewhere',
        'still',
        'such',
        'system',
        'take',
        'ten',
        'than',
        'that',
        'the',
        'their',
        'them',
        'themselves',
        'then',
        'thence',
        'there',
        'thereafter',
        'thereby',
        'therefore',
        'therein',
        'thereupon',
        'these',
        'they',
        'thick',
        'thin',
        'third',
        'this',
        'those',
        'though',
        'three',
        'through',
        'throughout',
        'thru',
        'thus',
        'to',
        'together',
        'too',
        'top',
        'toward',
        'towards',
        'twelve',
        'twenty',
        'two',
        'un',
        'under',
        'until',
        'up',
        'upon',
        'us',
        'very',
        'via',
        'was',
        'we',
        'well',
        'were',
        'what',
        'whatever',
        'when',
        'whence',
        'whenever',
        'where',
        'whereafter',
        'whereas',
        'whereby',
        'wherein',
        'whereupon',
        'wherever',
        'whether',
        'which',
        'while',
        'whither',
        'who',
        'whoever',
        'whole',
        'whom',
        'whose',
        'why',
        'will',
        'with',
        'within',
        'without',
        'would',
        'yet',
        'you',
        'your',
        'yours',
        'yourself',
        'yourselves'
    ];

    public static function startsWith( $string, $prefix ) {

        $len = strlen( $string );
        $prefixLen = strlen( $prefix );

        return $len >= $prefixLen && substr( $string, 0, $prefixLen ) === $prefix;
    }

    public static function endsWith( $string, $suffix ) {

        $len = strlen( $string );
        $suffixLen = strlen( $suffix );

        return $len >= $suffixLen && substr( $string, -$suffixLen ) === $suffix;
    }

    public static function contains( $string, $subString ) {

        return strpos( $string, $subString ) !== false;
    }

    public static function pluralize( $string ) {

        $lowerCased = strtolower( $string );

        foreach( self::$_uncountables as $uncountable )
            if( substr( $lowerCased, ( -1 * strlen( $uncountable ) ) ) == $uncountable )
                return $string;

        foreach( self::$_irregulars as $singular => $plural )
            if( preg_match( '/('.$singular.')$/i', $string, $matches ) )
                return preg_replace( '/('.$singular.')$/i', substr( $matches[ 0 ], 0, 1 ).substr( $plural, 1 ), $string );


        foreach( self::$_plurals as $rule => $replacement )
            if( preg_match( $rule, $string ) )
                return preg_replace( $rule, $replacement, $string );

        return $string;
    }

    public static function singularize( $string ) {

        $lowerCased = strtolower( $string );

        foreach( self::$_uncountables as $uncountable )
            if( substr( $lowerCased, ( -1 * strlen( $uncountable ) ) ) == $uncountable )
                return $string;

        foreach( self::$_irregulars as $singular => $plural )
            if( preg_match( '/('.$plural.')$/i', $string, $matches ) )
                return preg_replace( '/('.$plural.')$/i', substr( $matches[ 0 ], 0, 1 ).substr( $singular, 1 ), $string );


        foreach( self::$_singulars as $rule => $replacement )
            if( preg_match( $rule, $string ) )
                return preg_replace( $rule, $replacement, $string );

        return $string;
    }

    public static function rejoin( $string, $separator = ' ', $ignore = null ) {

        $ignore = $ignore ? preg_quote( $ignore, '/' ) : '';

        //All non-alphanumeric characters
        $string = preg_replace( [ '/[^a-z0-9'.$ignore.']/i' ], $separator, $string );

        //Between lowercase and UPPERCASE, e.g. some|Camel|Case|String
        //or uppercase notations, abbrevations etc., e.g. Xml|HTTP|Request
        $string = preg_replace( 
            [ '/([a-z])([A-Z])/', '/([A-Z]+)([A-Z])/' ],
            '$1'.$separator.'$2', 
            $string 
        );

        //finally remove repeating chars, so "something & something" wont end in "something---something"
        return preg_replace( '/'.$separator.'+/', $separator, $string );
    }

    public static function humanize( $string, $ignore = null ) {

        return ucwords( strtolower( self::rejoin( $string, ' ', $ignore ) ) );
    }

    public static function camelize( $string, $ignore = null ) {

        return str_replace( ' ', '', self::humanize( $string, $ignore ) );
    }

    public static function dasherize( $string, $ignore = null ) {

        return self::rejoin( $string, '-', $ignore );
    }

    public static function underscorize( $string, $ignore = null ) {

        return self::rejoin( $string, '_', $ignore );
    }

    public static function variablize( $string, $ignore = null ) {

        return lcfirst( self::camelize( $string, $ignore ) );
    }

    public static function tableize( $string, $ignore = null ) {

        return strtolower( self::underscorize( $string, $ignore ) );
    }

    public static function canonicalize( $string, $ignore = null ) {

        return strtolower( self::dasherize( $string, $ignore ) );
    }

    public static function slugify( $string, $ignore = null ) {

        $string = self::canonicalize( $string, $ignore );
        $stopWords = self::$_stopWords;
        $string = implode( '-', array_filter( explode( '-', $string ), function( $val ) use( $stopWords ) {

            return !in_array( $val, $stopWords );
        } ) );

        return $string;
    }

    public static function ordinalize( $string ) {
        
        $number = intval( $string );
        if( in_array( $number % 100, [ 11, 12, 13 ] ) )
            return $number.'th';

        switch( $number % 10 ) {
            case 1:  return $number.'st';
            case 2:  return $number.'nd';
            case 3:  return $number.'rd';
            default: return $number.'th';
        }
    }

    public static function sizify( $size, $base, array $units, $precision = 3 ) {

        $i = 0;
        foreach( $units as $unit ) {

            $currentSize = pow( $base, $i );
            $nextSize = pow( $base, $i + 1 );

            if( $size < $nextSize || $i >= count( $units ) - 1 )
                return round( $size / $currentSize, $precision ).$unit;

            $i++;
        }
    }

    public static function bytify( $size ) {

        return self::sizify( $size, 1024, [ 'Byte', 'KByte', 'MByte', 'GByte', 'TByte' ] );
    }

    public static function timify( $size ) {

        return self::sizify( $size, 1000, [ 'ms', 's' ] );
    }

    public static function resolve( $key, array $source, $defaultValue = null, $delimeter = '.' ) {

        $current = &$source;
        $keys = explode( $delimeter, $key );
        foreach( $keys as $key ) {

            if( is_numeric( $key ) )
                $key = intval( $key );

            if( !isset( $current[ $key ] ) )
                return $defaultValue;

            $current = &$current[ $key ];
        }

        return $current;
    }

    public static function interpolate( $string, array $source, $defaultValue = null, $delimeter = '.' ) {

        $del = preg_quote( $delimeter, '/' );
        return preg_replace_callback( '/\{\{([a-z0-9'.$del.']+)\}\}/i', function( $m ) use( $source, $defaultValue, $delimeter ) {

            return StringUtils::resolve( $m[ 1 ], $source, $defaultValue, $delimeter );
        }, $string );
    }

    public static function interpolateArray( array &$array, array &$source = null, $defaultValue = null, $delimeter = '.' ) {

        if( !$source )
            $source = &$array;

        foreach( $array as $key => &$val ) {

            if( is_array( $val ) )
                self::interpolateArray( $val, $source, $defaultValue, $delimeter );
            else if( is_string( $val ) ) {

                $array[ $key ] = self::interpolate( $val, $source, $defaultValue, $delimeter );
            }
        }
    }

    public static function map( $string, $delimeter, array $vars ) {

        $parts = explode( $delimeter, $string, count( $vars ) );

        $result = [];
        $x = 0;
        foreach( $vars as $name => $var ) 
            $result[ is_int( $name ) ? $var : $name ] = empty( $parts[ $x ] ) 
                                                      ? ( is_int( $name ) ? null : $var ) 
                                                      : $parts[ $x++ ];

        return $result;
    }

    public static function mapReverse( $string, $delimeter, array $vars ) {

        return array_map( 'strrev', self::map( strrev( $string ), $delimeter, $vars ) );
    }

    public static function parseUrl( $string ) {

        $parts = parse_url( $string );

        if( !$parts )
            throw new Exception( "Failed to parse URL $string, it seems it's not a valid URL" );

        return array_replace( [
            'scheme' => null,
            'user' => null,
            'pass' => null,
            'host' => null,
            'port' => null,
            'path' => null,
            'query' => null,
            'fragment' => null
        ], $parts );
    }

    public static function parseQuery( $string ) {

        $result = [];
        //Why the fuck doesn't parse_str return a "success"-boolean?
        //Can there be invalid query strings? Probably.
        parse_str( $string, $result );

        return $result;
    }

    public static function joinPath( $path, $subPath ) {

        $ds = \DIRECTORY_SEPARATOR;
        $path = self::normalizePath( $path );
        $subPath = self::normalizePath( $subPath );

        $subPath = $ds.ltrim( $subPath, $ds );

        return "$path$subPath";
    }

    public static function normalizePath( $path ) {

        $ds = \DIRECTORY_SEPARATOR;
        $path = str_replace( $ds === '\\' ? '/' : '\\', $ds, $path );
        $path = rtrim( $path, $ds );

        if( self::startsWith( $path, ".$ds" ) )
            $path = substr( $path, 2 );

        return $path;
    }
}