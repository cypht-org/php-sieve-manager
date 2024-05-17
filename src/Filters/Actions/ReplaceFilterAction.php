<?php

namespace PhpSieveManager\Filters\Actions;

class Replace implements FilterAction {
    private $mime;
    private $subject;
    private $from;
    private $replacement;

    public $require = ['replace'];

    /**
     * @param string $replacement - The replacement text
     * @param string|null $mime - Mime
     * @param string|null $subject - The subject of the message
     * @param string|null $from - The from address
     */
    public function __construct($replacement, $mime = null, $subject = null, $from = null) {
        $this->mime = $mime;
        $this->subject = $subject;
        $this->from = $from;
        $this->replacement = $replacement;
    }

    public function parse() {
        $script = "replace";
        if ($this->mime) {
            $script .= " :mime {$this->mime}";
        }
        if ($this->subject) {
            $script .= " :subject \"{$this->subject}\"";
        }
        if ($this->from) {
            $script .= " :from \"{$this->from}\"";
        }
        $script .= " \"{$this->replacement}\";\n";
        return $script;
    }
}