<?php

namespace PhpSieveManager\Filters\Actions;

class RejectFilterAction implements FilterAction
{
    private $reason;
    private $type;

    public $require = ['reject'];

    /**
     * @param string $reason
     * @param bool $ereject
     */
    public function __construct($reason, $ereject = false) {
        $this->reason = $reason;
        $this->type = $ereject ? 'ereject' : 'reject';
        $this->require[] = $this->type;
    }

    /**
     * @return string
     */
    public function parse() {
        return "{$this->type} \"{$this->reason}\";\n";
    }
}