<?php

namespace PhpSieveManager\Filters\Actions;

abstract class BaseRejectFilterAction extends BaseSieveAction
{
    public $require = [];

    protected function getRequiredParams()
    {
        return ['reason'];
    }

    protected function getParamTypes() {
        return ['reason' => 'string'];
    }

    /**
     * @return string
     */
    public function parse() {
        $type = $this->getType();
        $this->require[] = $type;
        return "{$type} \"{$type}\";\n";
    }

    abstract protected function getType();
}