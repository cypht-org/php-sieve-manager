<?php

namespace PhpSieveManager\Filters\Actions;

class DiscardFilterAction extends BaseSieveAction
{
    /**
     * @return string
     */
    public function parse() {
        return "discard;\n";
    }

    public function getRequiredParams() {
        return [];
    }

    protected function getParamTypes() {
        return [];
    }
}