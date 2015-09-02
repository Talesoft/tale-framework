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
}