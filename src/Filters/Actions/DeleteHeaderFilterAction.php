<?php

namespace PhpSieveManager\Filters\Actions;

/**
 * Please refer to https://datatracker.ietf.org/doc/html/rfc5293#section-5
 */
class DeleteHeaderFilterAction implements FilterAction
{
    private $index;
    private $last;
    private $comparator;
    private $matchType;
    private $fieldName;
    private $valuePatterns;

    public $require = ['editheader'];

    /**
     * @param string $fieldName - The field name of the header to delete
     * @param int|null $index - Index of the header to delete
     * @param bool $last - Flag to indicate if the last occurrence should be deleted
     * @param string|null $comparator - Comparator for matching
     * @param string|null $matchType - Type of match
     * @param array|null $valuePatterns - Patterns to match the header value
     */
    public function __construct($fieldName, $index = null, $last = false, $comparator = null, $matchType = null, $valuePatterns = []) {
        $this->index = $index;
        $this->last = $last;
        $this->comparator = $comparator;
        $this->matchType = $matchType;
        $this->fieldName = $fieldName;
        $this->valuePatterns = $valuePatterns;
    }

    /**
     * @return string
     */
    public function parse() {
        $script = "deleteheader";
        if ($this->index) {
            $script .= " :index {$this->index}";
            if ($this->last) {
                $script .= " :last";
            }
        }
        if ($this->comparator) {
            $script .= " {$this->comparator}";
        }
        if ($this->matchType) {
            $script .= " {$this->matchType}";
        }
        $script .= " \"{$this->fieldName}\"";
        if ($this->valuePatterns) {
            $script .= " [\"" . implode('", "', $this->valuePatterns) . "\"]";
        }
        $script .= ";\n";
        return $script;
    }
}