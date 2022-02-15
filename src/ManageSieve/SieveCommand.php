<?php

namespace PhpSieveManager\ManageSieve;

class SieveCommand
{
    public $name;
    public $args;
    public $withResponse;
    public $extralines;
    public $numLines;

    /**
     * @param $name
     * @param $args
     * @param $withResponse
     * @param $extralines
     * @param $numLines
     */
    public function __construct($name, $args=null, $withResponse=false, $extralines=null, $numLines=-1)
    {
        $this->name = $name;
        $this->args = $args;
        $this->withResponse = $withResponse;
        $this->extralines = $extralines;
        $this->numLines = $numLines;
    }
}