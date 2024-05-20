<?php

namespace PhpSieveManager\Filters\Actions;

abstract class BaseFlagFilterAction extends BaseSieveAction
{
    public $require = ['imap4flags'];

    public function getRequiredParams()
    {
        return ['list-of-flags'];
    }

    protected function getParamTypes() {
        return [
            'variablename' => 'string',
            'list-of-flags' => 'string-list'
        ];
    }

    /**
     * @return string
     */
    public function parse() {
        $script = $this->getScriptName();
        if (!empty($this->params['variablename'])) {
            $script .= "\"{$this->params['variablename']}\"";
        }
        $script .= " [" . implode(', ', array_map(function($flag) { return "\"$flag\""; }, $this->params['list-of-flags'])) . "];\n";

        return $script;
    }

    abstract public function getScriptName();
}