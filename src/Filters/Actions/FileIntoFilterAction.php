<?php

namespace PhpSieveManager\Filters\Actions;

class FileIntoFilterAction implements FilterAction
{
    private $mailbox;

    public $require = ['fileinto'];

    /**
     * @param string $mailbox - The mailbox to file the message into
     */
    public function __construct($mailbox) {
        $this->mailbox = $mailbox;
    }

    /**
     * @return string
     */
    public function parse() {
        return "fileinto \"{$this->mailbox}\";\n";
    }
}