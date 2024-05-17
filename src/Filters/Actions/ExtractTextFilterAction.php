<?php

namespace PhpSieveManager\Filters\Actions;

/**
 * Please refer to https://www.rfc-editor.org/rfc/rfc5703.html#page-11
 */
class ExtractTextFilterAction implements FilterAction
{
    private $modifier;
    private $first;
    private $varName;

    public $require = ['extracttext'];

    /**
     * @param string $varName - Variable name to store extracted text
     * @param string|null $modifier - Modifier for extraction
     * @param int|null $first - Number of first characters to extract
     */
    public function __construct($varName, $modifier = null, $first = null) {
        $this->modifier = $modifier;
        $this->first = $first;
        $this->varName = $varName;
    }

    /**
     * @return string
     */
    public function parse() {
        $script = "extracttext";
        if ($this->modifier) {
            $script .= " {$this->modifier}";
        }
        if ($this->first) {
            $script .= " :first {$this->first}";
        }
        $script .= " \"{$this->varName}\";\n";
        return $script;
    }
}