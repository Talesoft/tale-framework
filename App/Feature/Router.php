<?php

namespace Tale\App\Feature;

use Tale\App\Controller\Response;
use Tale\App\FeatureBase;
use Tale\Net\Http\StatusCode;
use Tale\Net\Mime\Type;
use Tale\App\Router as AppRouter;
use Tale\Net\Http\Request\Server as ServerRequest;
use Tale\App\Controller\Request;
use Tale\Proxy;

class Router extends FeatureBase {
    use Proxy\CallTrait;

    private $_router;

    public function run() {

        $this->setDefaultOptions( [
              'defaultController' => 'index',
              'defaultAction' => 'index',
              'defaultId' => null,
              'defaultFormat' => 'html',
              'routeHttpAndApply' => true,
              'routes' => [
                  '/:controller?/:action?/:id?.:format?' => [ $this, 'dispatchController' ]
              ]
        ], false );
        $config = $this->getConfig();

        $this->_router = new AppRouter( $config->routes->getOptions() );

        if( $config->routeHttpAndApply )
            $this->routeHttp()->apply();
    }

    public function getCallProxyTarget() {

        return $this->_router;
    }

    public function routeHttp( ServerRequest $request = null ) {

        $request = $request ? $request : new ServerRequest();
        $response = $request->createResponse();
        $url = $request->getUrl();
        $path = $url->getPath();
        $config = $this->getConfig();
        $app = $this->getApp();
        $appConfig = $app->getConfig();

        if( isset( $appConfig->urlBasePath ) ) {

            $basePath = $appConfig->urlBasePath;
            $len = strlen( $basePath );
            //Request was not in the base path directory
            if( strncmp( $path, $basePath, $len ) !== 0 )
                return null;

            $path = substr( $path, $len );
        }

        if( isset( $app->controllers ) ) {

            $app->controllers->setArg( 'httpRequest', $request );
            $app->controllers->setArg( 'httpResponse', $response );
        }

        $result = $this->route( $path );

        if( $result ) {

            if( $result instanceof Response\Redirect ) {

                $url = $result->getData();
                if( strncmp( $url, 'http', 4 ) !== 0 ) {

                    $url = $appConfig->url.( isset( $appConfig->urlBasePath ) ? $appConfig->urlBasePath : '/' ).ltrim( $url, '/' );

                    $response->setLocation( $url );
                    return $response;
                }
            }

            $body = $response->getBody();
            switch( $result->getFormat() ) {
                case 'json':

                    $body->setContentType( Type::JSON );
                    $body->setContent( json_encode( $result->getData() ) );
                    break;
                default:
                case 'html':

                    $data = $result->getData();

                    if( !is_string( $data ) )
                        $data = var_export( $data, true );

                    $body->setContentType( Type::HTML );
                    $body->setContent( $data );
            }
        }

        $response->setStatusCode( StatusCode::NOT_FOUND );
        return $response;
    }

    public function dispatchController( array $routeData = null ) {

        $config = $this->getConfig();
        $routeData = array_replace( [
            'controller' => $config->defaultController,
            'action' => $config->defaultAction,
            'id' => $config->defaultId,
            'format' => $config->defaultFormat
        ], $routeData ? $routeData : [] );

        //Only allow specific formats for security reasons.
        //TODO: Place this in a configuration (or rather, we would need some kind of Output Adapters for each type)
        if( !in_array( $routeData[ 'format' ], [ 'json', 'html' ] ) ) {

            $routeData[ 'format' ] = $config->defaultFormat;
        }

        $app = $this->getApp();
        if( isset( $app->controllers ) ) {

            return $app->controllers->dispatch( new Request( $routeData[ 'controller' ], $routeData[ 'action' ], $routeData[ 'format' ], [
                'id' => $routeData[ 'id' ]
            ] ) );
        }

        return null;
    }
}