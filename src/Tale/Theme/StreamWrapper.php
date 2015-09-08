<?php

namespace Tale\Theme;


class StreamWrapper
{

    /**
     * @var int
     */
    private $_position;
    /**
     * @var string
     */
    private $_data;

    /**
     * @param $path
     *
     * @return bool
     */
    public function stream_open($data)
    {
        $this->_data = substr($data, strpos($data, ';') + 1);
        $this->_position = 0;

        return true;
    }

    /**
     * @return null
     */
    public function stream_stat()
    {
        return null;
    }

    /**
     * @param $length
     *
     * @return string
     */
    public function stream_read($length)
    {
        $data = substr($this->_data, $this->_position, $length);
        $this->_position += strlen($data);

        return $data;
    }

    /**
     * @return int
     */
    public function stream_tell()
    {
        return $this->_position;
    }

    /**
     * @return bool
     */
    public function stream_eof()
    {
        return $this->_position >= strlen($this->_data);
    }
}