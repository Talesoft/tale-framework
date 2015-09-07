<?php

namespace Tale\App\Feature;

use Tale\App\Feature\Controller\Response;
use Tale\App\FeatureBase;
use Tale\Theme\Manager;

class View extends FeatureBase
{

    /**
     * @var \Tale\Theme\Manager
     */
    private $_themeManager;

    public function init()
    {

        $app = $this->getApp();

        if (!class_exists('Tale\\Theme\\Manager'))
            throw new \RuntimeException(
                "Failed to load theme feature: "
                ."The theme manager wasnt found. "
                ."Maybe you need the Tale\\Theme namespace?"
            );

        $this->prependOptions([
            'path'           => $this->getApp()->getOption('path').'/themes',
            'phtmlExtension' => 'phtml'
        ]);

        $app->bind('beforeRun', function () {

            $this->_themeManager = new Manager($this->getConfig()->getItems());

            /**
             * @var \Tale\App\Feature\Cache|null $cache
             */
            $cache = $this->cache;

            if ($cache)
                $this->_themeManager->setCacheManager($cache->getManager()->createSubManager('theme'));

            if (isset($this->controller)) {
#
                /**
                 * @var \Tale\App\Feature\Controller $controller
                 */
                $controller = $this->controller;

                $tm = $this->_themeManager;
                $controller->registerHelper('render', function ($controller, $path, array $args = null, $cacheHtml = false) use ($tm) {

                    return $tm->renderView($path, $args, $cacheHtml, $controller);
                });

                $controller->registerHelper('view', function ($controller, array $args = null, $path = null, $cacheHtml = false) {

                    if (!isset($controller->request) && !$path)
                        throw new \Exception(
                            "Failed to render view: controller has no "
                            ." dispatch information and you also didnt "
                            ." pass any path. I don't know what to view. "
                            ." Use the controller-feature's dispatch-methods."
                        );


                    if (isset($controller->request) && !$path) {

                        $req = $controller->request;

                        //Don't render anything if we didn't request html.
                        //Pass through the raw data instead
                        if ($req->getFormat() !== 'html')
                            return new Response($req->getFormat(), $args);

                        $path = str_replace('.', '/', $req->getController()).'/'.$req->getAction().'.'.$this->getOption('phtmlExtension');
                    }

                    return new Response('html', $controller->render($path, array_replace($args ? $args : [], $controller->getArgs()), $cacheHtml));
                });

                $controller->registerHelper('viewCached', function ($controller, array $args = null, $path = null) {

                    return $controller->view($args, $path, true);
                });

            }
        });

        $app->bind('afterRun', function () {

            unset($this->_themeManager);
        });
    }

    public function getDependencies()
    {

        return [
            'cache' => 'Tale\\App\\Feature\\Cache',
            'controller' => 'Tale\\App\\Feature\\Controller'
        ];
    }
}