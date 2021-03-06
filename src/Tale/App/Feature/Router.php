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
use Tale\Util\StringUtil;

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
                    if ($target[0] !== '@')
                        break;

                    $handler = $callback[1];
                    $target = substr($target, 1);
                    switch ($target) {
                        case 'router':

                            $routes[$route][0] = $this;
                            break;
                        default:

                            $routes[$route] = function (array $routeData) use($target, $handler) {

                                $routeData = call_user_func([$target, $handler], $routeData);

                                return $this->dispatchController($routeData);
                            };
                    }
                }
            }

            if (isset($this->controller)) {

                /**
                 * @var \Tale\App\Feature\Controller $controller
                 */
                $controller = $this->controller;
                $controller->registerHelper('getUrl', function ($controller, $path = null, array $args = null, $preserveQuery = false) {

                    $path = $path ? $path : '';

                    if (isset($controller->request)) {

                        /**
                         * @var \Tale\App\Feature\Controller\Request $req
                         */
                        $req = $controller->request;
                        $path = StringUtil::interpolate($path, [
                            'controller' => $req->getController(),
                            'action' => $req->getAction(),
                            'args' => $req->getArgs(),
                            'format' => $req->getFormat()
                        ]);
                    }

                    $url = $path;
                    $baseUrl = $this->getOption('baseUrl');
                    if ($baseUrl) {

                        $url = rtrim($baseUrl, '/').'/'.ltrim($path, '/');
                    }

                    $query = [];
                    if ($args)
                        $query = $args;

                    if ($preserveQuery && isset($controller->webRequest)) {

                        $query = array_replace_recursive($controller->webRequest->getUrlArgs(), $query);
                    }

                    if (!empty($query)) {

                        $url .= '?'.http_build_query($query);
                    }

                    return $url;
                });

                $controller->registerHelper('redirect', function($controller, $path, array $args = null, $preserveQuery = null) {

                    return new Controller\Response\Redirect($controller->getUrl($path, $args, $preserveQuery));
                });
            }

            $this->_router = new AppRouter($routes);
        });

        $app->bind('run', function () use($app) {

            if ($this->getOption('routeOnRun')) {

                if ($app->isCliApp())
                    $this->routeCliRequest();
                else {

                    $response = $this->routeHttpServerRequest();
                    $response->apply();
                }
            }
        });

        $app->bind('afterRun', function () {

            unset($this->_router);
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

        $result = $this->_router->route($path);

        if ($result) {

            if ($result instanceof Response\Redirect) {

                $url = $result->getData();
                $response->setLocation($url);

                return $response;
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
        $body = $response->getBody();
        $body->setContentType(Type::HTML);
        $body->setContent("$path not found");

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