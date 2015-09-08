<?php

namespace Tale\Crud\Type;

use Tale\Crud\UnsignedTypeBase;
use Tale\Crud\Validator;

class IntType extends UnsignedTypeBase
{

    const MIN = -2147483648;
    const MAX = 2147483647;
    const UNSIGNED_MIN = 0;
    const UNSIGNED_MAX = 4294967295;

    protected function convert($value)
    {

        if (empty($value) || !$this->isScalar())
            return null;

        return intval($value);
    }

    protected function validate(Validator $v)
    {

        $v->whenSet(function(Validator $v) {

            $v->notInt('Value has to be a number')
                ->when($this->isSigned(), function(Validator $v) {

                    $v->outOf(static::MIN, static::MAX, 'Value has to be between -127 and 127');
                })->otherwise(function(Validator $v) {

                    $v->outOf(static::UNSIGNED_MIN, static::UNSIGNED_MAX, 'Value has to be between 0 and 255');
                });
        });

        return parent::validate($v);
    }
}