<?php

namespace Tale\Crud;

class Validator
{

    private $_value;
    private $_empty;
    private $_required;
    /** @var Validator[] */
    private $_allowances;
    private $_otherwise;
    private $_errors;

    public function __construct($value)
    {

        $this->_value = $value;
        $this->_empty = is_null($value) || $value === '';
        $this->_required = false;
        $this->_allowances = [];
        $this->_otherwise = false;
        $this->_errors = [];
    }

    /**
     * @return mixed
     */
    public function getValue()
    {

        return $this->_value;
    }

    public function hasErrors()
    {

        return count($this->_errors) ? true : false;
    }

    /**
     * @return string[]
     */
    public function getErrors()
    {

        return $this->_errors;
    }

    public function addError($message)
    {

        $this->_errors[] = $message;

        return $this;
    }

    public function addErrors(array $errors)
    {

        $this->_errors = array_merge($this->_errors, $errors);

        return $this;
    }

    public function reset()
    {

        $this->_required = false;
        $this->_allowances = [];
        $this->_otherwise = false;
        $this->_errors = [];

        return $this;
    }

    public function when($condition, $validation)
    {

        if (!is_callable($validation))
            throw new \InvalidArgumentException("Argument 1 passed to Validator->when must be callable");

        if($condition) {
            call_user_func($validation, $this);
            $this->_otherwise = false;
        } else
            $this->_otherwise = true;

        return $this;
    }

    public function otherwise($validation)
    {

        if ($this->_otherwise)
            return $this->when(true, $validation);

        return $this;
    }

    public function whenSet($validation)
    {

        return $this->when(!is_null($this->_value), $validation);
    }

    public function allow($validation)
    {

        if (!is_callable($validation))
            throw new \InvalidArgumentException("Argument 1 passed to Validator->either must be callable");

        $v = new static($this->_value);
        call_user_func($validation, $v);

        $this->_allowances[] = $v;

        return $this;
    }

    public function notPassed($message)
    {


        $errors = [];
        foreach ($this->_allowances as $allowance) {

            if (!$allowance->hasErrors()) {

                return $this;
            }

            $errors = $allowance->getErrors();
        }

        $this->addErrors(array_merge($this->_errors, $errors));
        $this->addError($message);

        return $this;
    }


    public function isnt($condition, $message)
    {

        if (!$condition)
            $this->addError($message);

        return $this;
    }

    public function is($condition, $message)
    {

        return $this->isnt(!$condition, $message);
    }

    public function notSet($message)
    {

        $this->_required = true;
        return $this->is(is_null($this->_value), $message);
    }

    public function notEmpty($message)
    {

        return $this->is($this->_empty, $message);
    }

    public function smallerThan($min, $max = null, $message)
    {

        $len = $this->_empty ? 0 : strlen($this->_value);
        $min = $len >= $min;
        $max = $max ? $len <= $max : true;

        return $this->isnt($min && $max, $message);
    }

    public function outOf($min, $max, $message)
    {

        $int = $this->_empty ? 0 : intval($this->_value);

        return $this->isnt($int >= $min && $int <= $max, $message);
    }

    public function notIn(array $values, $message)
    {

        return $this->isnt(in_array($this->_value, $values, true), $message);
    }

    public function fails($filter, $message)
    {

        if ($this->_empty)
            return $this;

        return $this->isnt(filter_var($this->_value, $filter), $message);
    }

    public function notEmail($message)
    {

        return $this->fails(\FILTER_VALIDATE_EMAIL, $message);
    }

    public function notDateTime($message)
    {

        $result = \DateTime::createFromFormat(\DateTime::DATE_ATOM, $this->_value);
        return $this->isnt($result ? true : false, $message);
    }

    public function notInt($message)
    {

        return $this->fails(\FILTER_VALIDATE_INT, $message);
    }

    public function notFloat($message)
    {

        return $this->fails($message, \FILTER_VALIDATE_FLOAT);
    }

    public function notArray($message)
    {

        return $this->isnt(is_array($this->_value), $message);
    }

    public function notArrayOf($message, $className, $keyClassName = null)
    {

        $factory = Type::getTypeFactory();
        $className = $factory->resolveClassName($className);
        $keyClassName = $keyClassName ? $factory->resolveClassName($keyClassName) : null;

        if (!is_array($this->_value))
            return $this->addError($message);

        foreach ($this->_value as $key =>$value) {

            if (!is_a($value, $className))
                return $this->addError($message);

            if ($keyClassName && !is_a($key, $keyClassName))
                return $this->addError($message);
        }

        return $this;
    }

    public function notObject($message)
    {

        return $this->isnt(is_object($this->_value), $message);
    }

    public function notObjectOf($message, $className)
    {

        return $this->isnt(is_a($this->_value, $className), $message);
    }

    public function notClassNameOf($message, $className)
    {

        return $this->isnt(is_a($this->_value, $className, true), $message);
    }

    public function notIpv4($message)
    {

        return $this->fails(\FILTER_VALIDATE_IP | \FILTER_FLAG_IPV4, $message);
    }

    public function notIpv6($message)
    {

        return $this->fails(\FILTER_VALIDATE_IP | \FILTER_FLAG_IPV6, $message);
    }

    public function notMac($message)
    {

        return $this->fails(\FILTER_VALIDATE_MAC, $message);
    }

    public function notRegEx($message)
    {

        return $this->fails(\FILTER_VALIDATE_REGEXP, $message);
    }

    public function notUrl($message)
    {

        return $this->fails(\FILTER_VALIDATE_URL, $message);
    }

    public function mismatches($pattern, $message)
    {

        return $this->isnt(preg_match($pattern, is_string($this->_value) ? $this->_value : ''), $message);
    }

    public function notAlpha($message, $additionalChars = '')
    {

        return $this->mismatches("/^[a-z{$additionalChars}]+$/i", $message);
    }

    public function notAlphaNumeric($message, $additionalChars = '')
    {

        return $this->notAlpha($message, '0-9'.$additionalChars);
    }

    public function notCanonical($message, $additionalChars = '')
    {

        return $this->notAlphaNumeric($message, '_\-'.$additionalChars);
    }
}