<?php

namespace Tale;

use Exception;

/**
 * Static string utility class
 *
 * First argument should consistently be a string
 *
 * @package Tale
 */
class StringUtils {

    /**
     * Array of uncountable english words
     *
     * @var array
     */
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

    /**
     * Array of irregular english words
     * Keys are singular, values are plural representations
     *
     * @var array
     */
    private static $_irregulars = [
        'person' => 'people',
        'man' => 'men',
        'child' => 'children',
        'sex' => 'sexes',
        'move' => 'moves'
    ];

    /**
     * An array of plural translation patterns
     * Keys are RegEx patterns, values are replacements
     *
     * @var array
     */
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

    /**
     * An array of singular translation patterns
     * Keys are RegEx patterns, values are replacements
     *
     * @var array
     */
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

    /**
     * Array of english stop words for canonicalization
     *
     * @var array
     */
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

    /**
     * Returns the plural representation of a singular string
     * e.g. car => cars, house => houses, user-group => user-groups
     *
     * @param string $string The singular string to be translated
     *
     * @return string The plural representation of the passed singular string
     */
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

    /**
     * Returns the singular representation of a plural string
     * e.g. cars => car, houses => house, user-groups => user-group
     *
     * @param string $string The plural string to be translated
     *
     * @return string The singular representation of the passed singular string
     */
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

    /**
     * Takes a string, explodes it at any point except for alpha-numerical characters
     * (splitting it into single words)
     * and re-joins it with a different delimeter (Default: Space ( ))
     *
     * e.g. MyAwesomeClass      => My Awesome Class
     *      some_table_name     => some table name
     *      CONTENT_TYPE (-)    => CONTENT-TYPE
     *
     *
     * @todo There is a problem translating all-uppercase-strings right now,
     *       sometimes it's preferrable to strtolower() the string first
     *
     * @param string        $string    The subject string to be re-joined
     * @param string        $delimeter The delimeter to re-join single words with
     * @param string|null   $ignore    Characters to ignore
     *
     * @return string The re-joined string
     */
    public static function reJoin( $string, $delimeter = null, $ignore = null ) {

        $delimeter = !is_null( $delimeter ) ? $delimeter : ' ';
        $ignore = $ignore ? preg_quote( $ignore, '/' ) : '';

        //All non-alphanumeric characters
        $string = preg_replace( [ '/[^a-z0-9'.$ignore.']/i' ], $delimeter, $string );

        //Between lowercase and UPPERCASE, e.g. some|Camel|Case|String
        //or uppercase notations, abbrevations etc., e.g. Xml|HTTP|Request
        $string = preg_replace( 
            [ '/([a-z0-9])([A-Z])/', '/([A-Z]+)([A-Z])/' ],
            '$1'.$delimeter.'$2',
            $string 
        );

        //finally remove repeating chars, so "something & something" wont end in "something---something"
        return preg_replace( '/'.$delimeter.'+/', $delimeter, $string );
    }

    /**
     * Returns a "Human Readable" representation of a string
     * (Basically, reJoin paired with ucwords)
     *
     * e.g. SomeClassName   => Some Class Name
     *      some_table_name => Some Table Name
     *
     * @param string        $string The subject string
     * @param string|null   $ignore Characters to ignore in re-joinment
     *
     * @return string The "Human Readable" string
     */
    public static function humanize( $string, $ignore = null ) {

        return ucwords( strtolower( self::reJoin( $string, ' ', $ignore ) ) );
    }

    /**
     * Returns a CamelCased representation of a string
     * (Basically, humanize without the spaces between)
     *
     * Works best for inflecting strings to class-names
     *
     * e.g. Some String     => SomeString
     *      some_table_name => SomeTableName
     *
     * @param string        $string The subject string
     * @param string|null   $ignore Characters to ignore in re-joinment
     *
     * @return string The CamelCased string
     */
    public static function camelize( $string, $ignore = null ) {

        return str_replace( ' ', '', self::humanize( $string, $ignore ) );
    }

    /**
     * Returns a dash-separated representation of a string
     * (Normal reJoin with "-"-delimeter)
     * The casing returned is the same as the input string
     *
     * e.g. SomeClassName   => Some-Class-Name
     *      some_table_name => some-table-name
     *
     * @param string        $string The subject string
     * @param string|null   $ignore Characters to ignore in re-joinment
     *
     * @return string The dash-separated string
     */
    public static function dasherize( $string, $ignore = null ) {

        return self::reJoin( $string, '-', $ignore );
    }

    /**
     * Returns a underscore_separated representation of a string
     * (Normal reJoin with "_"-delimeter)
     * The casing returned is the same as the input string
     *
     * e.g. SomeClassName   => Some_Class_Name
     *      some-view-name  => some_view_name
     *
     * @param string        $string The subject string
     * @param string|null   $ignore Characters to ignore in re-joinment
     *
     * @return string The underscore_separated string
     */
    public static function underscorize( $string, $ignore = null ) {

        return self::rejoin( $string, '_', $ignore );
    }

    /**
     * Returns a camelCased string with the first word lowercased
     * (Basically, camelize paired with lcfirst)
     *
     * Works best for inflecting strings to variable- or method-names
     *
     * e.g. SomeClassName   => someClassName
     *      some_table_name => someTableName
     *
     * @param string        $string The subject string
     * @param string|null   $ignore Characters to ignore in re-joinment
     *
     * @return string The camelCased string
     */
    public static function variablize( $string, $ignore = null ) {

        return lcfirst( self::camelize( $string, $ignore ) );
    }

    /**
     * Returns a lower_cased_dash_separated string with lowercase characters
     * (Basically, underscorize paired with strtolower)
     *
     * Works best for inflecting strings to table-names in RDBMS
     *
     * e.g. SomeClassName   => some_class_name
     *      some-view-name  => some_view_name
     *
     * @param string        $string The subject string
     * @param string|null   $ignore Characters to ignore in re-joinment
     *
     * @return string The lower_cased_dash_separated string
     */
    public static function tableize( $string, $ignore = null ) {

        return strtolower( self::underscorize( $string, $ignore ) );
    }

    /**
     * Returns a lower-cased-dash-separated string with lowercase characters
     * (Basically, dasherize paired with strtolower)
     *
     * Works best for inflecting strings to file-names or slugs/mnemonic strings
     *
     * e.g. SomeClassName   => some-class-name
     *      some_table_name => some-table-name
     *
     * @param string        $string The subject string
     * @param string|null   $ignore Characters to ignore in re-joinment
     *
     * @return string The lower-cased-dash-separated string
     */
    public static function canonicalize( $string, $ignore = null ) {

        return strtolower( self::dasherize( $string, $ignore ) );
    }

    /**
     * Returns a lower-cased-dash-separated string with lowercase characters
     * This method automatically removes english stop-words from the string
     *
     * For a list of stop-words see $_stopWords
     *
     * Works best for inflecting strings to SEO-slugs
     *
     * e.g. SomeClassName   => class-name
     *      some_table_name => table-name
     *
     * @param string        $string The subject string
     * @param string|null   $ignore Characters to ignore in re-joinment
     *
     * @return string The lower-cased-dash-separated string
     */
    public static function slugify( $string, $ignore = null ) {

        $string = self::canonicalize( $string, $ignore );
        $stopWords = self::$_stopWords;
        $string = implode( '-', array_filter( explode( '-', $string ), function( $val ) use( $stopWords ) {

            return !in_array( $val, $stopWords );
        } ) );

        return $string;
    }

    /**
     * Creates a rd, nd, th representation of a number
     *
     * e.g. 1    => 1st
     *      2    => 2nd
     *      3    => 3rd
     *      4    => 4th
     *      123  => 123rd
     *
     * @param string|int    $string The input number or number-string
     *
     * @return string       The ordinalized representation of the input number
     */
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

    /**
     * Maps a number to a unit based on a base-number
     *
     * It's best to tale a look at the bytify/timify methods to understand this function.
     *
     * e.g. given 1000 as a $base and [ 'mm', 'm', 'km' ]
     *      200     => 200mm
     *      2000    => 2m
     *      2500    => 2.5km
     *      2000000 => 2km
     *
     * @todo Maybe try to make an array to $base instead of $base and $unit, e.g.
     *       [ 'ms', 1000 => 's', 60 => 'm', 60 => 'h', 24 => 'Days', 7 => 'Weeks', 52 => 'Years' ] etc.
     *
     * @param string|int   $number      The number to add a unit to
     * @param int          $base        The base number we're working on (1000 mostly, 60 for time, 1024 for bytes etc.)
     * @param array        $units       An array of units mapping on the units from low to high
     * @param int          $precision   The precision of float values that should be kept
     *
     * @return string The converted number with the unit appended
     */
    public static function sizify( $number, $base, array $units, $precision = 3 ) {

        $i = 0;
        foreach( $units as $unit ) {

            $currentSize = pow( $base, $i );
            $nextSize = pow( $base, $i + 1 );

            if( $number < $nextSize || $i >= count( $units ) - 1 )
                return round( $number / $currentSize, $precision ).$unit;

            $i++;
        }
    }

    /**
     * Automatically adds byte units to the number passed (input unit is Byte)
     *
     * @param int $size The byte size to bytify
     *
     * @return string The converted amount with the unit appended
     */
    public static function bytify( $size ) {

        return self::sizify( $size, 1024, [ 'Byte', 'KByte', 'MByte', 'GByte', 'TByte' ] );
    }

    /**
     * Automatically adds s and ms to time units (input unit is milliseconds (ms))
     *
     * @param int $size The milliseconds to timify
     *
     * @return string The converted amount with the unit appended
     */
    public static function timify( $size ) {

        return self::sizify( $size, 1000, [ 'ms', 's' ] );
    }

    /**
     * Resolves a key.subKey.subSubKey-style string to a deep array value
     *
     * The function accesses multi-dimensional keys with a delimeter given (Default: Dot (.))
     *
     * @protip If you want to throw an exception if no key is found, pass the exception as the default value
     *         and throw it, if the result is an Exception-type
     *
     * @param string        $key            The input key to operate on
     * @param array         $source         The array to search values in
     * @param mixed         $defaultValue   The default value if no key is found
     * @param string|null   $delimeter      The delimeter to access dimensions (Default: Dot (.))
     *
     * @return array|null The found value or the default value, if none found (Default: null)
     */
    public static function resolve( $key, array $source, $defaultValue = null, $delimeter = null ) {

        $delimeter = $delimeter ? $delimeter : '.';
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

    /**
     * Interpolates {{var.subVar}}-style based on a source array given
     *
     * Dimensions in the source array are accessed with a passed delimeter (Default: Dot (.))
     *
     * @param string        $string        The input string to operate on
     * @param array         $source        The associative source array
     * @param mixed         $defaultValue  The default value for indices that dont exist
     * @param string|null   $delimeter     The delimeter for multi-dimension access (Default: Dot (.))
     *
     * @return string The interpolated string with the variables replaced with their values
     */
    public static function interpolate( $string, array $source, $defaultValue = null, $delimeter = null ) {

        return preg_replace_callback( '/\{\{([^\}]+)\}\}/i', function( $m ) use( $source, $defaultValue, $delimeter ) {
            
            return StringUtils::resolve( $m[ 1 ], $source, $defaultValue, $delimeter );
        }, $string );
    }

    /**
     * Interpolates a multi-dimensional array with another array recursively
     *
     * If no source is given, you get a live interpolation where you can directly interpolate
     * variables that have just been interpolated before
     *
     * This is mostly used for option arrays, e.g. config-files
     *
     * @param array      $array         The array to interpolate (Passed by reference)
     * @param array|null $source        The source array for variables. If none given, the input array is taken
     * @param null       $defaultValue  The default value for indices that couldnt be resolved
     * @param string     $delimeter     The delimeter used for multi-dimension access (Default: Dot (.))
     */
    public static function interpolateArray( array &$array, array &$source = null, $defaultValue = null, $delimeter = null ) {

        //The source is also a reference to keep the source updated with interpolations at all times
        if( !$source )
            $source = &$array;

        foreach( $array as $key => &$val ) {

            if( is_array( $val ) )
                self::interpolateArray( $val, $source, $defaultValue, $delimeter );
            else if( is_string( $val ) ) {

                var_dump( "IPOL: $key => $val" );
                $array[ $key ] = self::interpolate( $val, $source, $defaultValue, $delimeter );
            }
        }
    }

    /**
     * This function works like a list( $a, $b ) = explode( ':', 'aValue:bValue' ) construct
     *
     * It maps the string delimeted by the passed delimeter to a passed array
     *
     * The passed array can either be numeric-indexed, using the value as the key (e.g. [ 'controller', 'action' ])
     * or it can be string-indexed, using the keys as the key and the value as the default value
     * (e.g. [ 'controller' => 'index', 'action' => 'index' ])
     * You can even mix both, the default-default value is null
     * (e.g. [ 'controller' => 'index', 'action' => 'index', 'id' ] )
     *
     * The result is a associative array using the keys of $vars and the values of the exploded $string-values
     *
     * This is primarily used to map e.g. urls to controllers, it somewhat works like a mini-router
     *
     * Imagine an url:
     * $url = "some-controller/some-action/some-id";
     *
     * Using map you can simply do:
     * $args = StringUtils::map( $url, '/', [ 'controller' => 'index', 'action' => 'index', 'id' ] );
     *
     * $args will have the following value:
     * $args === [
     *     'controller' => 'some-controller',
     *     'action' => 'some-action',
     *     'id' => 'some-id'
     * ];
     *
     * @param string    $string     The delimeted string to operator on
     * @param string    $delimeter  The delimeter to split the string by
     * @param array     $vars       The variables to map the string to
     *
     * @return array An associative array with the $string-values inserted
     */
    public static function map( $string, $delimeter, array $vars ) {

        $parts = explode( $delimeter, $string, count( $vars ) );

        $result = [];
        $x = 0;
        foreach( $vars as $name => $var ) {

            $index = is_int( $name ) ? $var : $name;

            $result[ $index ] = empty( $parts[ $x ] )
                              ? ( is_int( $name ) ? null : $var )
                              : $parts[ $x ];

            $x++;
        }

        return $result;
    }

    /**
     * The same as static::map(), but it works from the the end to the start of the string
     *
     * @see StringUtils::map
     *
     * @param string    $string     The delimeted string to operator on
     * @param string    $delimeter  The delimeter to split the string by
     * @param array     $vars       The variables to map the string to
     *
     * @return array An associative array with the $string-values inserted
     */
    public static function mapReverse( $string, $delimeter, array $vars ) {

        return array_map( 'strrev', self::map( strrev( $string ), $delimeter, $vars ) );
    }

    /**
     * A wrapper for parse_url that always contains all keys possible
     * The values that dont exist will be defaulted to null
     *
     * @param $string The URL to parse
     *
     * @return array The parse_url result with all keys existent
     * @throws Exception
     */
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

    /**
     * Normalizes and joins two path strings so that you always get a valid path
     * without worrying about where the slashes have to be put
     *
     * @param $path     The input path
     * @param $subPath  The sub path to append safely
     *
     * @return string The normalized, joined path
     */
    public static function joinPath( $path, $subPath ) {

        $ds = \DIRECTORY_SEPARATOR;
        $path = self::normalizePath( $path );
        $subPath = self::normalizePath( $subPath );

        $subPath = $ds.ltrim( $subPath, $ds );

        return "$path$subPath";
    }

    /**
     * Normalizes a path so it can be safely joined with other paths
     *
     * "Normal" is defined as the following:
     *
     * The directory separator is normalized to / (Windows can handle it just fine, natively)
     * Trailing slashes are removed (/directory/ is normalized to /directory)
     * If theres "./" or ".\", it is removed
     *
     * @param $path The path to normalize
     *
     * @return string The normalized path
     */
    public static function normalizePath( $path ) {

        $ds = \DIRECTORY_SEPARATOR;
        $path = str_replace( $ds === '\\' ? '/' : '\\', $ds, $path );
        $path = rtrim( $path, $ds );

        if( strncmp( $path, ".$ds", 3 ) === 0 )
            $path = substr( $path, 2 );

        return $path;
    }
}