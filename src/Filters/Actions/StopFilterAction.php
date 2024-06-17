<?php

namespace PhpSieveManager\Filters\Actions;

class StopFilterAction extends BaseFilterAction
{
    /**
     * @return string
     */
    public function parse() {
        return "stop;\n";
    }

    public function getRequiredParams() {
        return [];
    }

    protected function getParamTypes() {
        return [];
    }
}