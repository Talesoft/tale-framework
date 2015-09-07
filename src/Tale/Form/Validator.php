<?php

namespace Tale\Form;

class Validator
{

    private $_value;
    private $_empty;
    private $_errors;

    public function __construct($value)
    {

        $this->_value = $value;
        $this->_empty = is_null($value) || $value === '';
        $this->_errors = [];
    }

    /**
     * @return mixed
     */
    public function getValue()
    {

        return $this->_value;
    }

    /**
     * @return array
     */
    public function getErrors()
    {

        return $this->_errors;
    }


    public function isnt($condition, $message)
    {

        if (!$condition)
            $this->_errors[] = $message;

        return $this;
    }

    public function is($condition, $message)
    {

        return $this->isnt(!$condition, $message);
    }

    public function notSet($message)
    {

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

    public function notInt($message)
    {

        return $this->fails(\FILTER_VALIDATE_INT, $message);
    }

    public function notFloat($message)
    {

        return $this->fails($message, \FILTER_VALIDATE_FLOAT);
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

    public function notArray($message)
    {

        return $this->isnt(is_array($this->_value), $message);
    }
}