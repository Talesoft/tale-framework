<?php

namespace Tale\App\Feature;

use Tale\App\Controller\Response;
use Tale\App\ProxyFeatureBase;
use Tale\Theme\Manager;

class Themes extends ProxyFeatureBase {

    private $_manager;

    protected function init() {

        $app = $this->getApp();
        $manager = new Manager( array_replace_recursive( [
            'path' => $app->getConfig()->path.'/themes'
        ], $this->getConfig()->getOptions() ) );
        $this->_manager = $manager;

        if( isset( $app->controllers ) ) {

            $controllers = $app->controllers;
            $controllers->registerHelper( 'render', function( $path, array $args = null ) use( $manager ) {

                $realPath = $manager->resolveView( $path );

                if( !$realPath ) {

                    throw new \Exception( "Failed to find view $path" );
                }

                $render = function( $__path, $__args ) {

                    ob_start();
                    extract( $__args );
                    include $__path;

                    return ob_get_clean();
                };

                $render->bindTo( $this, $this );

                return $render( $realPath, $args ? $args : [] );
            } );

            $controllers->registerHelper( 'view', function( array $args = null, $path = null ) use( $manager ) {

                $req = $this->request;

                if( $req->getFormat() !== 'html' )
                    return new Response( $req->getFormat(), $args );

                //TODO: Make that .phtml at the end modular (Maybe via a default-format in ThemeManager or something)
                $path = $path ? $path : str_replace( '.', '/', $req->getController() ).'/'.$req->getAction().'.phtml';

                return new Response( 'html', $this->render( $path, $args ) );
            } );
        }
    }

    public function getTarget() {

        return $this->_manager;
    }
}