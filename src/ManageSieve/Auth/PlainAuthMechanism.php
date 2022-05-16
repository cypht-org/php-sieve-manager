<?php

namespace PhpSieveManager\ManageSieve\Auth;

use PhpSieveManager\ManageSieve\SieveCommand;

class PlainAuthMechanism extends BaseAuthMechanism
{
    /**
     * Generate Sieve command args for the Auth Mechanism
     *
     * @return SieveCommand
     */
    public function parse() {
        $args = base64_encode("\x00".$this->username."\x00".$this->password);
        return new SieveCommand(
            "AUTHENTICATE",
            ['"PLAIN"', '"'.$args.'"']
        );
    }
}