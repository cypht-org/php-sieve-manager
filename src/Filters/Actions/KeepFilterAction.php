<?php

namespace PhpSieveManager\Filters\Actions;

class KeepFilterAction extends BaseSieveAction
{
    /**
     * @return string
     */
    public function parse() {
        return "keep;\n";
    }

    public function getRequiredParams()
    {
        return [];
    }

    public function getParamTypes()
    {
        return [];
    }
}