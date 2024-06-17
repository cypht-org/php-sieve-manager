<?php

namespace PhpSieveManager\Filters\Actions;

/**
 * Please refer to https://datatracker.ietf.org/doc/rfc5435/
 */
class NotifyFilterAction extends BaseFilterAction
{
    public $require = ['enotify'];

    protected function getRequiredParams()
    {
        return ['method'];
    }

    protected function getParamTypes() {
        return [
            'from' => 'string',
            'importance' => 'int',
            'options' => 'string-list',
            'message' => 'string',
            'fcc' => 'string',
            'method' => 'string',
        ];
    }

    /**
     * @return string
     */
    public function parse() {
        $script = "notify";
        if (!empty($this->params['from'])) {
            $script .= " :from \"{$this->params['from']}\"";
        }
        if (!empty($this->params['importance'])) {
            $script .= " :importance \"{$this->params['importance']}\"";
        }
        if (!empty($this->params['options'])) {
            $script .= " :options [\"" . implode('", "', $this->params['options']) . "\"]";
        }
        if (!empty($this->params['fcc'])) {
            $this->require[] = 'fcc';
            $script .= " :fcc \"{$this->params['fcc']}\"";
        }
        if (!empty($this->params['message'])) {
            $script .= " :message \"{$this->params['message']}\"";
        }
        $script .= " \"{$this->params['method']}\";\n";
        return $script;
    }
}