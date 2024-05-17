<?php

namespace PhpSieveManager\Filters\Actions;

abstract class BaseFlagFilterAction implements FilterAction
{
    public $require = ['imap4flags'];

    private $variableName;
    private $listOfFlags;

    /**
     * @param array $listOfFlags - List of flags
     * @param string $variableName - Variable name
     */
    public function __construct($listOfFlags, $variableName = null) {
        $this->listOfFlags = $listOfFlags;
        $this->variableName = $variableName;
        if ($variableName) {
            $this->require[] = 'variables';
        }
    }

    abstract public function getScriptName();

    /**
     * @return string
     */
    public function parse() {
        $script = $this->getScriptName();
        if ($this->variableName) {
            $script .= "\"{$this->variableName}\"";
        }
        $script .= " [" . implode(', ', array_map(function($flag) { return "\"$flag\""; }, $this->listOfFlags)) . "];\n";

        return $script;
    }
}