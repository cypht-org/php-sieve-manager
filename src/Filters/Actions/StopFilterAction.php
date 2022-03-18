<?php

namespace PhpSieveManager\Filters\Actions;

class StopFilterAction implements FilterAction
{
    /**
     * @param array $params
     */
    public function __construct(array $params = []) {}

    /**
     * @return string
     */
    public function parse() {
        return "stop;";
    }
}