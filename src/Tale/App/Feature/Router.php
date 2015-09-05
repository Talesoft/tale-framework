<?php

namespace Tale\App\Feature;

use Tale\App\Feature\Controller\Response;
use Tale\App\FeatureBase;
use Tale\Net\Http\Body;
use Tale\Net\Http\StatusCode;
use Tale\Net\Mime\Type;
use Tale\App\Router as AppRouter;
use Tale\Net\Http\Request\Server as ServerRequest;
use Tale\App\Feature\Controller\Request;

class Router extends FeatureBase
{

    /**
     * @var \Tale\App\Router $router
     */
    private $_router;

    public function init()
    {

        $this->prependOptions([
            'defaultController' => 'index',
            'defaultAction'     => 'index',
            'defaultId'         => null,
            'defaultFormat'     => 'html',
            'formats'           => ['html','json' /*TODO: moar formats!*/],
            'routeOnRun'        => true,
            'baseUrl'           => null,
            'routes'            => [
                '/:controller?/:action?/:id?.:format?' => ["@router", 'dispatchController']
            ]
        ]);

        $app = $this->getApp();

        $app->bind('beforeRun', function () {

            $routes = $this->getOption('routes');

            //Parse route targets
            foreach ($routes as $route => $callback) {

                if (is_array($callback) && is_string($callback[0])) {

                    $target = $callback[0];
                    switch ($target) {
                        case '@router':

                            $routes[$route][0] = $this;
                            break;
                        //TODO: Come on, there can be featuritis here
                    }
                }
            }

            $this->_router = new AppRouter($routes);

            var_dump('ROUTER LOADED');
        });

        $app->bind('run', function () use($app) {

            if ($this->getOption('routeOnRun')) {

                if ($app->isCliApp())
                    $this->routeCliRequest();
                else {

                    $response = $this->routeHttpServerRequest();

                    var_dump('RESP', $response);

                    $response->apply();
                }
            }

            var_dump('ROUTER RAN');
        });

        $app->bind('afterRun', function () {

            unset($this->_router);

            var_dump('ROUTER UNLOADED');
        });
    }

    public function getDependencies() {

        return [
            'controller' => 'Tale\\App\\Feature\\Controller'
        ];
    }

    public function routeHttpServerRequest(ServerRequest $request = null)
    {

        $request = $request ? $request : new ServerRequest();
        $response = $request->createResponse();
        $url = $request->getUrl();
        $path = rtrim($url->getPath(), '/');

        $baseUrl = $this->getOption('baseUrl');

        if ($baseUrl) {

            $basePath = rtrim(parse_url($baseUrl, \PHP_URL_PATH), '/');

            if ($basePath !== '/') {

                $len = strlen($basePath);
                //Request was not in the base path directory
                if (strncmp($path, $basePath, $len) !== 0) {

                    $response->setStatusCode(StatusCode::NOT_FOUND);
                    $body = new Body();
                    $body->setContent("$basePath not in $path");
                    $response->setBody($body);
                    return $response;
                }

                $path = substr($path, $len + 1);
            }
        }

        $path = '/'.ltrim($path, '/');

        if (isset($this->controller)) {

            $this->controller->setArg('webRequest', $request);
            $this->controller->setArg('webResponse', $response);
        }

        var_dump("ROUTE $path");
        $result = $this->_router->route($path);
        var_dump("RR", $result);

        if ($result) {

            if ($result instanceof Response\Redirect) {

                $url = $result->getData();
                if (strncmp($url, 'http', 4) !== 0) {

                    $url = rtrim($baseUrl, '/').'/'.ltrim($url, '/');
                    $response->setLocation($url);

                    return $response;
                }
            }

            $body = $response->getBody();
            switch ($result->getFormat()) {
                case 'json':

                    $body->setContentType(Type::JSON);
                    $body->setContent(json_encode($result->getData()));
                    return $response;
                default:
                case 'html':

                    $data = $result->getData();

                    if (!is_string($data))
                        $data = var_export($data, true);

                    $body->setContentType(Type::HTML);
                    $body->setContent($data);
                    return $response;
            }
        }

        $response->setStatusCode(StatusCode::NOT_FOUND);
        $body = new Body();
        $body->setContent("$path not found");
        $response->setBody($body);

        return $response;
    }

    public function routeCliRequest()
    {

        throw new \Exception("Not implemented");
    }

    public function dispatchController(array $routeData = null)
    {

        if (!isset($this->controller))
            throw new \RuntimeException(
                "Failed to dispatch controller: controller feature "
                ."is not loaded"
            );

        $routeData = array_replace([
            'controller' => $this->getOption('defaultController'),
            'action'     => $this->getOption('defaultAction'),
            'id'         => $this->getOption('defaultId'),
            'format'     => $this->getOption('defaultFormat')
        ], $routeData ? $routeData : []);

        //Only allow specific formats for security reasons.
        //TODO: Place this in a configuration (or rather, we would need some kind of Output Adapters for each type)
        if (!in_array($routeData['format'], $this->getOption('formats'))) {

            $routeData['format'] = $this->getOption('defaultFormat');
        }

        return $this->controller->dispatch(new Request($routeData['controller'], $routeData['action'], $routeData['format'], [
            'id' => $routeData['id']
        ]));
    }
}