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
        $args = base64_encode(implode('\0', [$this->authz_id, $this->username, $this->password]));
        return new SieveCommand(
            "AUTHENTICATE",
            ['"PLAIN"', '"'.$args.'"']
        );
    }
}