<?php

namespace PhpSieveManager\Filters\Actions;

class RedirectFilterAction extends BaseSieveAction
{
    protected function getRequiredParams()
    {
        return ['address'];
    }

    protected function getParamTypes() {
        return ['address' => 'string'];
    }

    /**
     * @return string
     */
    public function parse() {
        return "redirect \"{$this->params['address']}\";\n";
    }
}