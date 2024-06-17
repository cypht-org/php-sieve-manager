<?php

namespace PhpSieveManager\Filters\Actions;

/**
 * Please refer to https://www.rfc-editor.org/rfc/rfc5703.html#page-11
 */
class ExtractTextFilterAction extends BaseFilterAction
{
    public $require = ['extracttext'];

    protected function getRequiredParams()
    {
        return ['varname'];
    }

    protected function getParamTypes() {
        return [
            'modifier' => 'string',
            'first' => 'int',
            'varname' => 'string'
        ];
    }

    /**
     * @return string
     */
    public function parse() {
        $script = "extracttext";
        if (!empty($this->params['modifier'])) {
            $script .= " {$this->params['modifier']}";
        }
        if (!empty($this->params['first'])) {
            $script .= " :first {$this->params['first']}";
        }
        $script .= " \"{$this->params['varname']}\";\n";
        return $script;
    }
}