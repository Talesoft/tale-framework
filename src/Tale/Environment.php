<?php

namespace Tale;

class Environment
{

    public static function getOption($name, $default = null)
    {

        if (!isset($_ENV[$name]))
            return $default;

        return $_ENV[$name];
    }

    public static function getOptions()
    {

        return $_ENV;
    }

    public static function getClientOption($name, $default = null)
    {

        if (!isset($_SERVER[$name]))
            return $default;

        return $_SERVER[$name];
    }

    public static function getClientOptions()
    {

        return $_SERVER;
    }

    public static function getArg($name, $shortHand = null, $optional = null, $default = null)
    {

        $shortHand = $shortHand ? $shortHand : $name[0];

        $addOn = '';
        if ($optional === false)
            $addOn .= ':';

        if ($optional === true)
            $addOn .= '::';

        $opt = getopt($shortHand.$addOn, [$name.$addOn]);

        if (isset($opt[$shortHand]))
            return $opt[$shortHand];

        if (isset($opt[$name]))
            return $opt[$name];

        return $default;
    }

    public static function getArgs()
    {

        return self::getClientOption('argv');
    }

    public static function isWeb()
    {

        return !(self::isCli() || self::isServer());
    }

    public static function isCli()
    {

        return \PHP_SAPI !== 'cli';
    }

    public static function isServer()
    {

        return \PHP_SAPI !== 'cli-server';
    }
}