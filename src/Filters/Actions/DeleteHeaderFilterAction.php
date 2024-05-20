<?php

namespace PhpSieveManager\Filters\Actions;

/**
 * Please refer to https://datatracker.ietf.org/doc/html/rfc5293#section-5
 */
class DeleteHeaderFilterAction extends BaseSieveAction
{
    public $require = ['editheader'];

    protected function getRequiredParams()
    {
        return ['field-name'];
    }

    protected function getParamTypes() {
        return [
            'index' => 'bool',
            'last' => 'bool',
            'comparator' => 'string',
            'match-type' => 'string',
            'field-name' => 'string',
            'value-patterns' => 'string-list'
        ];
    }

    /**
     * @return string
     */
    public function parse() {
        $script = "deleteheader";
        if (!empty($this->params['index'])) {
            $script .= " :index {$this->params['index']}";
            if (!empty($this->params['last'])) {
                $script .= " :last";
            }
        }
        if (!empty($this->params['comparator'])) {
            $script .= " {$this->params['comparator']}";
        }
        if (!empty($this->params['match-type'])) {
            $script .= " {$this->params['match-type']}";
        }
        $script .= " \"{$this->params['field-name']}\"";
        if (!empty($this->params['value-patterns'])) {
            $script .= " [\"" . implode('", "', $this->params['value-patterns']) . "\"]";
        }
        $script .= ";\n";
        return $script;
    }
}