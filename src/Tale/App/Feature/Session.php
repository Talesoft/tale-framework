<?php

namespace Tale\App\Feature;

use Tale\App\FeatureBase;
use Tale\Session\Manager as SessionManager;

class Session extends FeatureBase
{

    /**
     * @var \Tale\Session\Manager
     */
    private $_manager;

    public function init()
    {
        $app = $this->getApp();

        if (!class_exists('Tale\\Session\\Manager'))
            throw new \RuntimeException(
                "Failed to load session feature: "
                ."The session manager wasnt found. "
                ."Maybe you need the Tale\\Session namespace?"
            );

        $app->bind('beforeRun', function () {

            $config = $this->getConfig();
            $this->_manager = new SessionManager($config->getItems());

            if (isset($this->controller))
                $this->controller->setArg('session', $this->_manager);
        });

        $app->bind('afterRun', function () {

            unset($this->_manager);
        });
    }

    public function getManager()
    {

        return $this->_manager;
    }

    public function getDependencies() {

        return [
            'controller' => 'Tale\\App\\Feature\\Controller'
        ];
    }
}