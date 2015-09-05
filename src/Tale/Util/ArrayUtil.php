<?php
/**
 * ArrayUtil - The Tale Framework
 *
 * @version 1.0
 * @stability Development
 * @author Torben Köhn <tk@talesoft.io>
 *
 * This software is distributed under the MIT license.
 * A copy of the license has been distributed with this software.
 * If this is not the case, you can read the license text here:
 * http://licenses.talesoft.io/2015/MIT.txt
 *
 * Please do not remove this comment block. Thank you.
 */

namespace Tale\Util;

use Tale\Util;
use Tale\Dom\Xml\Element as XmlElement;

class ArrayUtil extends Util
{

    /**
     * Interpolates a multi-dimensional array with another array recursively
     *
     * If no source is given, you get a live interpolation where you can directly interpolate
     * variables that have just been interpolated before
     *
     * Don't fetch the return value, the arrays are references.
     * If you want an unmutable version, rather take ArrayUtil::interpolate
     *
     * This is mostly used for option arrays, e.g. config-files
     *
     * We take both arrays as a reference to achieve a live-interpolation effect.
     * You can work with values you just interpolated before
     * e.g. [ 'path' => '{{rootPath}}/my/path', 'subPaths' => [ '{{path}}/sub/path' ] ]
     *
     * If you want to keep the original array, take a copy before
     *
     * @param array      $array        The array to interpolate (Passed by reference)
     * @param array|null $source       The source array for variables. If none given, the input array is taken
     * @param null       $defaultValue The default value for indices that couldnt be resolved
     * @param string     $delimeter    The delimeter used for multi-dimension access (Default: Dot (.))
     *
     * @return array The interpolated array (Notice that it's just the same reference to the array you passed
     */
    public static function interpolateMutable(array &$array, array &$source = null, $defaultValue = null, $delimeter = null)
    {

        if (!$source)
            $source = &$array;

        foreach ($array as $key => &$val) {

            if (is_array($val))
                self::interpolateMutable($val, $source, $defaultValue, $delimeter);
            else if (is_string($val)) {

                $array[$key] = StringUtil::interpolate($val, $source, $defaultValue, $delimeter);
            }
        }

        return $array;
    }

    public static function interpolate(array $array, array &$source = null, $defaultValue = null, $delimeter = null)
    {

        //We just do this to have a mutable and an unmutable version, here $array will be a copy of the passed array
        //whereas interpolateMutable will accept it as a reference and work directly on the passed array
        //If you use this version, the return value contains your interpolated array, not the passed array
        return self::interpolateMutable($array, $source, $defaultValue, $delimeter);
    }

    public static function mergeSort(&$array, $comparator = null)
    {

        //I got this from here:
        //http://www.php.net/manual/en/function.usort.php#38827
        //Thank you, sreid at sea-to-sky dot net

        $comparator = $comparator ? $comparator : 'strcmp';

        //Arrays of size < 2 require no action.
        if (count($array) < 2)
            return;

        //Split the array in half
        $half = round(count($array) / 2);
        $first = array_slice($array, 0, $half);
        $second = array_slice($array, $half);

        //Recurse to sort the two halves
        self::mergeSort($first, $comparator);
        self::mergeSort($second, $comparator);

        //If all of $first is <= all of $second, just append them.
        if (call_user_func($comparator, end($first), $second[0]) < 1) {

            $array = array_merge($first, $second);
            return;
        }

        //Merge the two sorted arrays into a single sorted array
        $array = [];
        $i = $j = 0;
        while ($i < count($first) && $j < count($second)) {
            if (call_user_func($comparator, $first[$i], $second[$j]) < 1) {
                $array[] = $first[$i++];
            } else {
                $array[] = $second[$j++];
            }
        }

        //Merge the remainder
        while ($i < count($first)) $array[] = $first[$i++];
        while ($j < count($second)) $array[] = $second[$j++];

        return;
    }


    /**
     * Loads an array from a given file name
     *
     * json => json_decode
     * php => include
     * yml? => Tale\Yaml\Parser
     * xml => Tale\Dom\Xml\Parser
     *
     * @param string $path The path of the array file to load
     *
     * @throws \Exception
     *
     * @return array The array parsed from the file
     */
    public static function fromFile($path)
    {

        $ext = pathinfo($path, \PATHINFO_EXTENSION);

        $items = null;
        switch ($ext) {
            default:
            case 'php':

                $items = include($path);
                break;
            case 'json':

                //Special tale flavor?
                //Allows for //-style comments line-wise
                $json = implode('', array_filter(array_map('trim', file($path)), function ($line) {

                    return strpos($line, '//') !== 0;
                }));

                $items = json_decode($json, true);
                break;
            case 'xml':

                $items = XmlElement::fromFile($path)->getArray();
                break;
        }

        if (!is_array($items))
            throw new \Exception("Failed to load array from file: $path doesnt contain a valid array");

        return $items;
    }
}