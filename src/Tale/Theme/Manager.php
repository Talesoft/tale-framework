<?php

namespace Tale\Theme;

use Tale\Config;
use Tale\Cache;
use Tale\Util\StringUtil;

class Manager
{
    use Config\OptionalTrait;
    use Cache\OptionalTrait;

    const TYPE_FONT = 'fonts';
    const TYPE_IMAGE = 'images';
    const TYPE_STYLE = 'styles';
    const TYPE_SCRIPT = 'scripts';
    const TYPE_VIEW = 'views';

    private static $_id = 0;

    private $_themes;

    public function __construct(array $options = null)
    {

        $this->appendOptions([
            'path'          => './themes',
            'themes'        => ['default'],
            'types' => [
                'fonts'   => 'fonts',
                'images'  => 'images',
                'scripts' => 'scripts',
                'styles'  => 'styles',
                'views'   => 'views'
            ],
            'minify'        => true,
            'lifeTime'      => 0,
            //TODO: Maybe we could need a Converter Factory here?
            'converters' => [
                self::TYPE_IMAGE  => [
                    'png' => [
                        'php' => 'Tale\\Theme\\Converter\\Png\\Php'
                    ]
                ],
                self::TYPE_STYLE  => [
                    'css' => [
                        'cssx' => 'Tale\\Theme\\Converter\\Css\\CssX',
                        'less' => 'Tale\\Theme\\Converter\\Css\\Less',
                        'sass' => 'Tale\\Theme\\Converter\\Css\\Sass',
                        'styl' => 'Tale\\Theme\\Converter\\Css\\Stylus'
                    ]
                ],
                self::TYPE_SCRIPT => [
                    'js' => [
                        'ts'     => 'Tale\\Theme\\Converter\\Js\\TypeScript',
                        'coffee' => 'Tale\\Theme\\Converter\\Js\\CoffeeScript'
                    ]
                ],
                self::TYPE_VIEW   => [
                    'phtml' => [
                        'jade'  => 'Tale\\Theme\\Converter\\Phtml\\Jade',
                        'xtpl'  => 'Tale\\Theme\\Converter\\Phtml\\Xtpl',
                        'twig'  => 'Tale\\Theme\\Converter\\Phtml\\Twig',
                        'blade' => 'Tale\\Theme\\Converter\\Phtml\\Blade'
                    ]
                ]
            ]
        ]);

        if ($options)
            $this->appendOptions($options, true);

        $this->_wrapperName = 'tale-theme-'.(self::$_id++);
        $this->_themes = $this->_config->themes;

        stream_wrapper_register($this->_wrapperName, 'Tale\\Theme\\StreamWrapper');
    }

    public function __destruct()
    {

        stream_wrapper_unregister($this->_wrapperName);
    }

    public function addTheme($theme)
    {

        $this->_themes[] = $theme;

        return $this;
    }

    public function getPossiblePaths($type, $path)
    {

        $types = $this->getOption('types');
        $subPath = $types[$type];
        $themePath = $this->getOption('path');

        foreach ($this->_themes as $theme) {

            yield "$themePath/$theme/$subPath/$path";
        }
    }

    public function getExistingPaths($type, $path)
    {

        foreach ($this->getPossiblePaths($type, $path) as $fullPath)
            if (file_exists($fullPath))
                yield $fullPath;
    }

    public function resolve($type, $path)
    {

        foreach ($this->getExistingPaths($type, $path) as $fullPath)
            return $fullPath;

        return null;
    }

    public function getConvertedContent($type, $path)
    {

        $converters = $this->getOption('converters');
        if (isset($converters[$type])) {

            $ext = pathinfo($path, \PATHINFO_EXTENSION);
            $exts = $converters[$type];

            if (isset($exts[$ext])) {

                $convs = $exts[$ext];
                foreach ($convs as $sourceExt => $className) {

                    $inputPath = dirname($path).'/'.basename($path, ".$ext").'.'.$sourceExt;

                    var_dump("CONV $inputPath");

                    if ($fullInputPath = $this->resolve($type, $inputPath)) {

                        $converter = new $className($this, [
                            'paths' => array_map('dirname', iterator_to_array($this->getPossiblePaths($type,$inputPath)))
                        ]);
                        return $converter->convert($fullInputPath, $path);
                    }
                }
            }
        }

        if ($fullPath = $this->resolve($type, $path))
            return file_get_contents($fullPath);

        return null;
    }

    public function resolveFont($path)
    {

        return $this->resolve(self::TYPE_FONT, $path);
    }

    public function resolveImage($path)
    {

        return $this->resolve(self::TYPE_IMAGE, $path);
    }

    public function resolveScript($path)
    {

        return $this->resolve(self::TYPE_SCRIPT, $path);
    }

    public function resolveStyle($path)
    {

        return $this->resolve(self::TYPE_STYLE, $path);
    }

    public function resolveView($path)
    {

        return $this->resolve(self::TYPE_VIEW, $path);
    }

    public function renderView($path, array $args = null, $cacheHtml = false, $context = null)
    {

        $renderView = function() use($path, $args, $context) {

            $phtml = $this->fetchCached(
                'views.'.StringUtil::canonicalize($path),
                function () use ($path) {

                return $this->getConvertedContent(self::TYPE_VIEW, $path);
            }, $this->getOption('lifeTime'));

            if( !$phtml )
                throw new \Exception(
                    "Failed to convert theme: Neither $path nor"
                    ." any convertible file could be found"
                );

            $dataUri = "$this->_wrapperName://data;$phtml";

            $render = function ($__dataUri, $__args) {

                ob_start();
                extract($__args);
                include $__dataUri;

                return ob_get_clean();
            };

            if (is_object($context))
                $render->bindTo($context, $context);

            return $render($dataUri, $args ? $args : []);
        };

        if (!$cacheHtml)
            return $renderView();

        return $this->fetchCached('views.html.'.StringUtil::canonicalize($path), $renderView, $this->getOption('lifeTime'));
    }
}