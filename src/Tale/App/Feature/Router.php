<?php

namespace Tale\App\Feature;

use Tale\App\Feature\Controller\Response;
use Tale\App\FeatureBase;
use Tale\Net\Http\StatusCode;
use Tale\Net\Mime\Type;
use Tale\App\Router as AppRouter;
use Tale\Net\Http\Request\Server as ServerRequest;
use Tale\App\Feature\Controller\Request;
use Tale\Net\Url;

class Router extends FeatureBase
{

    private $_router;

    public function init()
    {

        $this->prependOptions([
            'defaultController' => 'index',
            'defaultAction'     => 'index',
            'defaultId'         => null,
            'defaultFormat'     => 'html',
            'routeOnRun'        => true,
            'baseUrl'           => '/',
            'routes'            => [
                '/:controller?/:action?/:id?.:format?' => [$this, 'dispatchController']
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
                        case '@controller':
                            $routes[$route][0] = $this->controller;
                            break;
                    }
                }
            }

            $this->_router = new AppRouter($this->getOption('routes'));

            var_dump('ROUTER LOADED');
        });

        $app->bind('run', function () use($app) {

            if ($this->getOption('routeOnRun')) {

                if ($app->isCliApp())
                    $this->routeCliRequest();
                else
                    $this->routeServerRequest();
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


    public function routeServerRequest(ServerRequest $request = null)
    {

        $request = $request ? $request : new ServerRequest();
        $response = $request->createResponse();
        $url = $request->getUrl();
        $path = $url->getPath();
        $app = $this->getApp();
        $appConfig = $app->getConfig();

        $baseUrl = $this->getOption('baseUrl');

        if ($baseUrl) {

            $basePath = parse_url($baseUrl, \PHP_URL_PATH);

            $len = strlen($basePath);
            //Request was not in the base path directory
            if (strncmp($path, $basePath, $len) !== 0)
                return null;

            $path = substr($path, $len);
        }

        if (isset($this->controller)) {

            $this->controller->setArg('webRequest', $request);
            $this->controller->setArg('webResponse', $response);
        }

        $result = $this->_router->route($path);

        if ($result) {

            if ($result instanceof Response\Redirect) {

                $url = $result->getData();
                if (strncmp($url, 'http', 4) !== 0) {

                    $url = $appConfig->url.(isset($appConfig->urlBasePath) ? $appConfig->urlBasePath : '/').ltrim($url, '/');

                    $response->setLocation($url);

                    return $response;
                }
            }

            $body = $response->getBody();
            switch ($result->getFormat()) {
                case 'json':

                    $body->setContentType(Type::JSON);
                    $body->setContent(json_encode($result->getData()));
                    break;
                default:
                case 'html':

                    $data = $result->getData();

                    if (!is_string($data))
                        $data = var_export($data, true);

                    $body->setContentType(Type::HTML);
                    $body->setContent($data);
            }
        }

        $response->setStatusCode(StatusCode::NOT_FOUND);

        return $response;
    }

    public function dispatchController(array $routeData = null)
    {

        $config = $this->getConfig();
        $routeData = array_replace([
            'controller' => $config->defaultController,
            'action'     => $config->defaultAction,
            'id'         => $config->defaultId,
            'format'     => $config->defaultFormat
        ], $routeData ? $routeData : []);

        //Only allow specific formats for security reasons.
        //TODO: Place this in a configuration (or rather, we would need some kind of Output Adapters for each type)
        if (!in_array($routeData['format'], ['json', 'html'])) {

            $routeData['format'] = $config->defaultFormat;
        }

        $app = $this->getApp();
        if (isset($app->controllers)) {

            return $app->controllers->dispatch(new Request($routeData['controller'], $routeData['action'], $routeData['format'], [
                'id' => $routeData['id']
            ]));
        }

        return null;
    }
}