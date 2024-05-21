<?php

namespace PhpSieveManager\Filters\Actions;

class StopFilterAction extends BaseSieveAction
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