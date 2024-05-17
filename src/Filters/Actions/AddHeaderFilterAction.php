<?php

namespace PhpSieveManager\Filters\Actions;

/**
 * Please refer to https://datatracker.ietf.org/doc/html/rfc5293#section-4
 */
class AddHeaderFilterAction implements FilterAction
{
    private $last;
    private $fieldName;
    private $value;

    public $require = ['editheader'];

    /**
     * @param string $fieldName - The field name of the header to add
     * @param string $value - The value of the header to add
     * @param bool $last - Flag to indicate if the header should be added at the end
     */
    public function __construct($fieldName, $value, $last = false) {
        $this->last = $last;
        $this->fieldName = $fieldName;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function parse() {
        $script = "addheader";
        if ($this->last) {
            $script .= " :last";
        }
        $script .= " \"{$this->fieldName}\" \"{$this->value}\";\n";
        return $script;
    }
}