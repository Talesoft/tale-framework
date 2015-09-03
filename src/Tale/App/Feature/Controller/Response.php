<?php

namespace Tale\App\Feature\Controller;

class Response extends Message
{

    private $_data;

    public function __construct($format, $data = null)
    {
        parent::__construct($format);

        $this->_data = $data;
    }

    /**
     * @return null
     */
    public function getData()
    {

        return $this->_data;
    }
}