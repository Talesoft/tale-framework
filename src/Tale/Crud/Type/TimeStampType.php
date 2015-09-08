<?php

namespace Tale\Crud\Type;

use Tale\Crud\TypeBase;
use Tale\Crud\Validator;

class TimeStampType extends TypeBase
{

    protected function sanitize($value)
    {

        if (empty($value))
            return null;

        $time = intval($value);

        return new \DateTime("@$time");
    }

    protected function validate(Validator $v)
    {

        $v->whenSet(function(Validator $v) {

            $v->notInt('The value needs to be an int representing the UNIX time stamp');
        });

        return parent::validate($v);
    }
}