<?php

namespace PhpSieveManager\Filters\Actions;

class KeepFilterAction implements FilterAction
{
    /**
     * @param array $params
     */
    public function __construct(array $params = []) {}

    /**
     * @return string
     */
    public function parse() {
        return "keep;"."\n";
    }
}