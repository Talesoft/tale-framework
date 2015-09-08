<?php

namespace Tale\Crud\Type;

use Tale\Crud\TypeBase;
use Tale\Crud\Validator;

class BoolType extends TypeBase
{

    protected function sanitize($value)
    {

        if (empty($value) || !$this->isScalar())
            return false;

        if ($this->isString()) {
            switch (strtolower($value)) {
                case 'yes':
                case 'true':
                case 'on':
                case '1':

                    return true;
                case 'no':
                case 'false':
                case 'off':
                case '0':

                    return false;
            }
        }

        return intval($value) ? true : false;
    }

    protected function validate(Validator $v)
    {

        $v->whenSet(function(Validator $v) {

            $v->notIn(
                [
                    true, false, 0, 1, '0', '1', 'yes',
                    'true', 'on', 'no', 'false', 'off'
                ],
                'The value needs to be one of 0/1/yes/true/on/no/false/off'
            );
        });

        return parent::validate($v);
    }
}