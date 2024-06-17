<?php

namespace PhpSieveManager\Filters\Actions;

/**
 * Please refer to https://datatracker.ietf.org/doc/html/rfc5293#section-4
 */
class AddHeaderFilterAction extends BaseFilterAction
{
    public $require = ['editheader'];

    public function getRequiredParams()
    {
        return ['field-name', 'value'];
    }

    protected function getParamTypes() {
        return [
            'last' => 'bool',
            'field-name' => 'string',
            'value' => 'string'
        ];
    }

    /**
     * @return string
     */
    public function parse() {
        $script = "addheader";
        if (!empty($this->params['last'])) {
            $script .= " :last";
        }
        $script .= " \"{$this->params['field-name']}\" \"{$this->params['value']}\";\n";
        return $script;
    }
}