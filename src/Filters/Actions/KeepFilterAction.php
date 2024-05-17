<?php

namespace PhpSieveManager\Filters\Actions;

class KeepFilterAction implements FilterAction
{
    /**
     * @return string
     */
    public function parse() {
        return "keep;\n";
    }
}