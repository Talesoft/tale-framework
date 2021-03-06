<?php

namespace Tale\App\Feature\Controller;

use Tale\Util\StringUtil;

class Request extends Message
{

    private $_controller;
    private $_action;
    private $_args;

    public function __construct($controller, $action, $format, array $args = null)
    {
        parent::__construct($format);

        //Sanitize names for consistent use
        $this->_controller = implode('.', array_map('Tale\\Util\\StringUtil::canonicalize', explode('.', $controller)));
        $this->_action = StringUtil::canonicalize($action);
        $this->_args = $args ? $args : [];
    }

    /**
     * @return string
     */
    public function getController()
    {

        return $this->_controller;
    }

    /**
     * @return array
     */
    public function getAction()
    {

        return $this->_action;
    }

    /**
     * @return array
     */
    public function getArgs()
    {

        return $this->_args;
    }
}