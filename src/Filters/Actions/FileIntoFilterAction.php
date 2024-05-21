<?php

namespace PhpSieveManager\Filters\Actions;

class FileIntoFilterAction extends BaseSieveAction
{
    public $require = ['fileinto'];

    protected function getRequiredParams()
    {
        return ['mailbox'];
    }

    protected function getParamTypes() {
        return [
            'mailbox' => 'string',
            'flags' => 'string-list',
            'copy' => 'bool',
            'mailboxid' => 'string',
            'create' => 'bool',
            'specialuse' => 'string',
        ];
    }

    /**
     * @return string
     */
    public function parse() {
        $script = "fileinto";
        if (!empty($this->params['special-use-attr'])) {
            $this->require[] = 'special-use';
            $script .= " :specialuse \"{$this->params['specialuse']}\"";
        }
        if (!empty($this->params['create'])) {
            $this->require[] = 'mailbox';
            $script .= " :create";
        }
        if (!empty($this->params['mailboxid'])) {
            $this->require[] = 'mailboxid';
            $script .= " :mailboxid \"{$this->params['mailboxid']}\"";
        }
        if (!empty($this->params['copy'])) {
            $this->require[] = 'copy';
            $script .= " :copy";
        }
        if (!empty($this->params['flags'])) {
            $this->require[] = 'imap4flags';
            $script .= " :flags \"" . implode('", "', $this->params['flags']) . "\"";
        }
        $script .= " \"{$this->params['mailbox']}\";\n";
        return $script;
    }
}