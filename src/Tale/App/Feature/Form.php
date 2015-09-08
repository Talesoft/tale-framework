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

                $controller->setArg('formData', null);
                $controller->registerHelper('loadFormData', function($controller) {

                    if ($controller->formData !== null)
                        return;

                    $controller->formData = [
                        Method::POST => [],
                        Method::PUT => [],
                        Method::GET => []
                    ];
                    if (isset($controller->webRequest)) {

                        //We can get data from the web request
                        $bodyArgs = $controller->webRequest->getBodyArgs();
                        $urlArgs = $controller->webRequest->getUrlArgs();
                        $controller->formData[Method::POST] = $bodyArgs;
                        $controller->formData[Method::PUT] = $bodyArgs;
                        $controller->formData[Method::GET] = $urlArgs;
                    }
                });

                $controller->registerHelper('hasFormData', function($controller, $method) {

                    $controller->loadFormData();

                    return !empty($controller->formData[$method]);
                });

                $controller->registerHelper('getFilledForm', function ($controller, $name, $method = Method::POST, ServerRequest $request = null) use($app) {

                    $form = $this->_manager->getForm($name);

                    if (!$controller->hasFormData($method))
                        return $form;

                    foreach ($controller->formData[$method] as $name => $value)
                        if (isset($form->{$name}))
                            $form->{$name}->setValue($value);

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