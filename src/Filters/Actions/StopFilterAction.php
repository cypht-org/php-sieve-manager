<?php

namespace PhpSieveManager\Filters\Actions;

class StopFilterAction implements FilterAction
{
    /**
     * @return string
     */
    public function parse() {
        return "stop;";
    }
}