<?php

namespace PhpSieveManager\Filters\Actions;

class RedirectFilterAction extends BaseFilterAction
{
    public $require = [];

    protected function getRequiredParams()
    {
        return ['address'];
    }

    protected function getParamTypes() {
        return [
            'address' => 'string',
            'copy' => 'bool',
            'notify' => 'string',
            'ret' => 'string',
        ];
    }

    /**
     * @return string
     */
    public function parse() {
        $script = "redirect";
        if (!empty($this->params['copy'])) {
            $this->require[] = 'copy';
            $script .= " :copy";
        }
        if (!empty($this->params['notify'])) {
            $this->require[] = 'redirect-dsn';
            $script .= " :notify \"{$this->params['notify']}\"";
        }
        if (!empty($this->params['ret'])) {
            $script .= " :ret \"{$this->params['ret']}\"";
        }
        $script .= " \"{$this->params['address']}\";\n";
        return $script;
    }
}