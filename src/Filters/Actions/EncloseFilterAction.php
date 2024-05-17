<?php

namespace PhpSieveManager\Filters\Actions;

/**
 * Please refer to https://www.rfc-editor.org/rfc/rfc5703.html#section-6
 */
class EncloseFilterAction implements FilterAction
{
    private $subject;
    private $headers;
    private $content;

    public $require = ['enclose'];

    /**
     * @param string $content - The content to enclose
     * @param string $subject - The subject of the enclosed message
     * @param array $headers - List of headers
     */
    public function __construct($content, $subject, $headers) {
        $this->subject = $subject;
        $this->headers = $headers;
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function parse() {
        return "enclose :subject \"{$this->subject}\" :headers [\"" . implode('", "', $this->headers) . "\"] \"{$this->content}\";\n";
    }
}