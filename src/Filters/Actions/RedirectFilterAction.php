<?php

namespace PhpSieveManager\Filters\Actions;

class RedirectFilterAction implements FilterAction
{
    private $address;

    /**
     * @param string $address - The address to redirect the message to
     */
    public function __construct($address) {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function parse() {
        return "redirect \"{$this->address}\";\n";
    }
}