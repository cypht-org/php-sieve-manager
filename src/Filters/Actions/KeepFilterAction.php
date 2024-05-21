<?php

namespace PhpSieveManager\Filters\Actions;

class KeepFilterAction extends BaseSieveAction
{
    public $require = [];

    public function getRequiredParams()
    {
        return [];
    }

    public function getParamTypes()
    {
        return ['flags' => 'string-list'];
    }

    /**
     * @return string
     */
    public function parse() {
        $flags = '';
        if (!empty($this->params['flags'])) {
            $this->require[] = 'imap4flags';
            $flags = " :flags \"" . implode('", "', $this->params['flags']) . "\"";
        }
        return "keep{$flags};\n";
    }
}