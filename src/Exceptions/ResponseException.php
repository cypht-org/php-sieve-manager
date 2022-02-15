<?php

namespace PhpSieveManager\Exceptions;


class ResponseException extends \Exception
{
    public $code;
    public $data;

    public function __construct($code = "", $data = "")
    {
        $this->data = $data;
        $this->code = $code;
        parent::__construct($data, 0);
    }
}