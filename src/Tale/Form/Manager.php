<?php

namespace Tale\Form;

use Tale\Form;
use Tale\Config;

class Manager
{
    use Config\OptionalTrait;

    private $_forms;

    public function __construct(array $options = null)
    {

        $this->appendOptions([
            'forms' => []
            //More to come?
        ]);
        $this->appendOptions($options);

        $this->_forms = [];

        foreach ($this->getOption('forms') as $name => $fieldDefinitions)
            $this->addForm($name, $fieldDefinitions);
    }

    public function addForm($name, array $fieldDefinitions)
    {

        $this->_forms[$name] = new Form($fieldDefinitions);

        return $this;
    }

    public function getForm($name)
    {

        return $this->_forms[$name];
    }

    public function __get($formName)
    {

        return $this->getForm($formName);
    }
}