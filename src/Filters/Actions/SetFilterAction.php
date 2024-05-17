<?php

namespace PhpSieveManager\Filters\Actions;

/**
 * Please refer to https://www.rfc-editor.org/rfc/rfc5229.html
 */
class SetFilterAction implements FilterAction
{
    private $modifier;
    private $name;
    private $value;

    public $require = ['variables'];

    /**
     * @param string $name - The name to set
     * @param string $value - The value to set
     * @param string|null $modifier - Modifier for the set action
     */
    public function __construct($name, $value, $modifier = null) {
        $this->modifier = $modifier;
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function parse() {
        $script = "set";
        if ($this->modifier) {
            $script .= " {$this->modifier}";
        }
        $script .= " \"{$this->name}\" \"{$this->value}\";\n";
        return $script;
    }
}