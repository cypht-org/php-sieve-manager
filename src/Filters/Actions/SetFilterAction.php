<?php

namespace PhpSieveManager\Filters\Actions;

/**
 * Please refer to https://www.rfc-editor.org/rfc/rfc5229.html
 */
class SetFilterAction extends BaseFilterAction
{
    public $require = ['variables'];

    protected function getRequiredParams()
    {
        return ['name', 'value'];
    }

    protected function getParamTypes() {
        return [
            'name' => 'string',
            'value' => 'string',
            'modifier' => 'string',
        ];
    }

    /**
     * @return string
     */
    public function parse() {
        $script = "set";
        if (!empty($this->params['modifier'])) {
            $script .= " {$this->params['modifier']}";
        }
        $script .= " \"{$this->params['name']}\" \"{$this->params['value']}\";\n";
        return $script;
    }
}