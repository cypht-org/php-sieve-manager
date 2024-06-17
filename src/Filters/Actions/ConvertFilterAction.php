<?php

namespace PhpSieveManager\Filters\Actions;

/**
 * Please refer to https://www.rfc-editor.org/rfc/rfc6558.html
 */
class ConvertFilterAction extends BaseFilterAction
{
    public $require = ['convert'];

    protected function getRequiredParams()
    {
        return array_keys($this->getParamTypes());
    }

    protected function getParamTypes() {
        return [
            'quoted-from-media-type' => 'string',
            'quoted-to-media-type' => 'string',
            'transcoding-params' => 'string-list'
        ];
    }

    /**
     * @return string
     */
    public function parse() {
        return "convert \"{$this->params['quoted-from-media-type']}\" \"{$this->params['quoted-to-media-type']}\" [" . implode(', ', array_map(function($param) { return "\"$param\""; }, $this->params['transcoding-params'])) . "];\n";
    }
}