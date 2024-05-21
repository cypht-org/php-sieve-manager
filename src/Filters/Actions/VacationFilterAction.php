<?php

namespace PhpSieveManager\Filters\Actions;

class VacationFilterAction extends BaseSieveAction
{
    public $require = ['vacation'];

    protected function getParamTypes() {
        return [
            'days' => 'int',
            'seconds' => 'int',
            'subject' => 'string',
            'from' => 'string',
            'addresses' => 'string-list',
            'mime' => 'bool',
            'handle' => 'string',
            'reason' => 'string'
        ];
    }

    protected function getRequiredParams() {
        return ['reason'];
    }

    /**
     * @return string
     */
    public function parse() {
        $script = "vacation";
        if (!empty($this->params['seconds'])) {
            $script .= " :seconds {$this->params['seconds']}";
            $this->require[] = 'vacation-seconds';
        } elseif (!empty($this->params['days'])) {
            $script .= " :days {$this->params['days']}";
        }
        if (!empty($this->params['subject'])) {
            $script .= " :subject \"{$this->params['subject']}\"";
        }
        if (!empty($this->params['from'])) {
            $script .= " :from \"{$this->params['from']}\"";
        }
        if (!empty($this->params['addresses'])) {
            $script .= " :addresses [\"" . implode('", "', $this->params['addresses']) . "\"]";
        }
        if (!empty($this->params['mime'])) {
            $script .= " :mime {$this->params['mime']}";
        }
        if (!empty($this->params['handle'])) {
            $script .= " :handle \"{$this->params['handle']}\"";
        }
        $script .= " \"{$this->params['reason']}\";\n";
        return $script;
    }
}