<?php

namespace Tale\Theme;

use Tale\Config;

class Manager {

    private $_activeThemes;
    private $_config;

    public function __construct( array $options = null ) {

        $this->_config = new Config( array_replace_recursive( [
             'path' => './themes',
             'activeThemes' => [ 'default' ],
             'resourceTypes' => [
                 'fonts' => 'fonts',
                 'images' => 'images',
                 'scripts' => 'scripts',
                 'styles' => 'styles',
                 'views' => 'views'
             ],
             'minify' => true,
             'lifeTime' => 0,
             'preprocessors' => [
                 'images' => [
                     'png' => [
                         'php' => 'Tale\\Theme\\Preprocessor\\Png\\Php'
                     ]
                 ],
                 'styles' => [
                     'css' => [
                         'cssx' => 'Tale\\Theme\\Preprocessor\\Css\\CssX',
                         'less' => 'Tale\\Theme\\Preprocessor\\Css\\Less',
                         'sass' => 'Tale\\Theme\\Preprocessor\\Css\\Sass',
                         'styl' => 'Tale\\Theme\\Preprocessor\\Css\\Stylus'
                     ]
                 ],
                 'views' => [
                     'phtml' => [
                         'jade' => 'Tale\\Theme\\Preprocessor\\Phtml\\Jade',
                         'xtpl' => 'Tale\\Theme\\Preprocessor\\Phtml\\Xtpl',
                         'twig' => 'Tale\\Theme\\Preprocessor\\Phtml\\Twig',
                         'blade' => 'Tale\\Theme\\Preprocessor\\Phtml\\Blade'
                     ]
                 ],
                 'scripts' => [
                     'js' => [
                         'ts' => 'Tale\\Theme\\Preprocessor\\Js\\TypeScript',
                         'coffee' => 'Tale\\Theme\\Preprocessor\\Js\\CoffeeScript'
                     ]
                 ]
             ]
         ], $options ? $options : [] ) );

        $this->_activeThemes = $this->_config->activeThemes->getOptions();
    }

    public function getConfig() {

        return $this->_config;
    }

    public function addTheme( $theme ) {

        $this->_activeThemes[] = $theme;

        return $this;
    }

    private function _process( $type, $path ) {

        if( !isset( $this->_config->preprocessors->{$type} ) )
            return $path;

        $ext = pathinfo( $path, \PATHINFO_EXTENSION );
        $exts = $this->_config->preprocessors->{$type};

        if( !isset( $exts->{$ext} ) )
            return $path;

        $processors = $exts->{$ext};

        //TODO: Caching now works on a extension level, a file with the same name but different extension will be created
        //TODO: Rework this into Tale\Cache (Will probably only work with File adapter, which sucks...)

        foreach( $processors as $sourceExt => $className ) {

            $sourcePath = dirname( $path ).'/'.basename( $path, ".$ext" ).'.'.$sourceExt;

            if( file_exists( $sourcePath ) ) {

                $processor = new $className( $this );
                $processor->process( $sourcePath, $path );

                return $sourcePath;
            }
        }

        return $path;
    }

    public function resolve( $type, $path, $process = true ) {

        $config = $this->getConfig();
        $subPath = $config->resourceTypes->{$type};

        foreach( $this->_activeThemes as $theme ) {

            $filePath = "{$config->path}/$theme/$subPath/$path";

            if( file_exists( $filePath ) && time() - filemtime( $filePath ) <= $this->_config->lifeTime )
                return $filePath;

            if( $process )
                $this->_process( $type, $filePath );

            if( file_exists( $filePath ) ) {

                touch( $filePath );
                return $filePath;
            }
        }

        return null;
    }

    public function resolveFont( $path, $process = true ) {

        return $this->resolve( 'fonts', $path, $process );
    }

    public function resolveImage( $path, $process = true ) {

        return $this->resolve( 'images', $path, $process );
    }

    public function resolveScript( $path, $process = true ) {

        return $this->resolve( 'scripts', $path, $process );
    }

    public function resolveStyle( $path, $process = true ) {

        return $this->resolve( 'styles', $path, $process );
    }

    public function resolveView( $path, $process = true ) {

        return $this->resolve( 'views', $path, $process );
    }
}