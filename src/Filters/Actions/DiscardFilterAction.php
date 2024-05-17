<?php

namespace PhpSieveManager\Filters\Actions;

class DiscardFilterAction implements FilterAction
{
    /**
     * @return string
     */
    public function parse() {
        return "discard;\n";
    }
}