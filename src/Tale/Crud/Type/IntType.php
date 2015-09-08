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

    protected function sanitize($value)
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

                    $v->outOf(
                        static::MIN, static::MAX,
                        sprintf('Value has to be between %d and %d', static::MIN, static::MAX
                    ));
                })->otherwise(function(Validator $v) {

                    $v->outOf(
                        static::UNSIGNED_MIN, static::UNSIGNED_MAX,
                        sprintf('Value has to be between %d and %d', static::UNSIGNED_MIN, static::UNSIGNED_MAX
                    ));
                });
        });

        return parent::validate($v);
    }
}