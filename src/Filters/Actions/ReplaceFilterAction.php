<?php

namespace PhpSieveManager\Filters\Actions;

class ReplaceFilterAction extends BaseFilterAction
{
    public $require = ['replace'];

    protected function getRequiredParams()
    {
        return ['replacement'];
    }

    protected function getParamTypes() {
        return [
            'mime' => 'string',
            'subject' => 'int',
            'from' => 'string-list',
            'replacement' => 'string',
        ];
    }

    public function parse() {
        $script = "replace";
        if (!empty($this->params['mime'])) {
            $script .= " :mime {$this->params['mime']}";
        }
        if (!empty($this->params['subject'])) {
            $script .= " :subject \"{$this->params['subject']}\"";
        }
        if (!empty($this->params['from'])) {
            $script .= " :from \"{$this->params['from']}\"";
        }
        $script .= " \"{$this->params['replacement']}\";\n";
        return $script;
    }
}