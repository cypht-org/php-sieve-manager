<?php

namespace PhpSieveManager\Filters\Actions;

class DiscardFilterAction implements FilterAction
{
    /**
     * @param array $params
     */
    public function __construct(array $params = []) {}

    /**
     * @return string
     */
    public function parse() {
        return "discard;"."\n";
    }
}