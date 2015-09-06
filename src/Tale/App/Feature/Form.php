<?php

namespace Tale\App\Feature;

use Tale\App\FeatureBase;
use Tale\Form\Manager;
use Tale\Net\Http\Method;
use Tale\Net\Http\Request\Server as ServerRequest;

class Form extends FeatureBase
{

    /**
     * @var \Tale\Form\Manager
     */
    private $_manager;

    public function init()
    {

        if (!class_exists('Tale\\Form\\Manager'))
            throw new \RuntimeException(
                "Failed to load form feature: "
                ."The form manager class wasnt found. "
                ."Maybe you need the Tale\\Form namespace?"
            );

        $app = $this->getApp();
        $app->bind('beforeRun', function () use ($app) {

            $this->_manager = new Manager($this->getConfig()->getItems());

            /**
             * @var \Tale\App\Feature\Controller|null $controller
             */
            $controller = $this->controller;

            if ($controller) {

                $controller->setArg('forms', $this->_manager);

                $controller->registerHelper('addForm', function($controller, $name, $fieldDefinitions) {

                    $this->_manager->addForm($name, $fieldDefinitions);

                    return $controller;
                });

                $controller->registerHelper('getForm', function($controller, $name) {


                    return $this->_manager->getForm($name);
                });

                $controller->registerHelper('getFilledForm', function ($controller, $name, $method = Method::POST, ServerRequest $request = null) use($app) {

                    $form = $this->_manager->getForm($name);

                    //This links to the webRequest that the router gave us.
                    //Does it make sense here? Lets think about this
                    if (!$request && isset($controller->webRequest))
                        $request = $controller->webRequest;

                    if (!$request) {

                        if ($app->isWebApp())
                            throw new \RuntimeException(
                                "Failed to fill form: "
                                ."no HTTP request found to get data from. "
                                ."Try dispatching via the router feature."
                            );

                        //TODO: CLI form handling
                    }

                    switch ($method) {
                        case Method::GET:

                            foreach ($request->getUrlArgs() as $name => $value) {

                                if (isset($form->{$name}))
                                    $form->{$name}->setValue($value);
                            }
                            break;
                        case Method::POST:
                        case Method::PUT:

                            foreach ($request->getBodyArgs() as $name => $value) {

                                if (isset($form->{$name}))
                                    $form->{$name}->setValue($value);
                            }
                            break;
                        default:

                            throw new \Exception("Unsupported form method");
                    }

                    return $form;
                });

            }
        });

        $app->bind('afterRun', function () {

            unset($this->_manager);
        });
    }

   public function getManager()
   {

       return $this->_manager;
   }

    public function getDependencies()
    {

        return [
            'cache' => 'Tale\\App\\Feature\\Cache',
            'controller' => 'Tale\\App\\Feature\\Controller'
        ];
    }
}