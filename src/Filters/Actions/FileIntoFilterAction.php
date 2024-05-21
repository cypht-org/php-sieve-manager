<?php

namespace PhpSieveManager\Filters\Actions;

class FileIntoFilterAction extends BaseSieveAction
{
    public $require = ['fileinto'];

    protected function getRequiredParams()
    {
        return ['mailbox'];
    }

    protected function getParamTypes() {
        return ['mailbox' => 'string'];
    }

    /**
     * @return string
     */
    public function parse() {
        return "fileinto \"{$this->params['mailbox']}\";\n";
    }
}