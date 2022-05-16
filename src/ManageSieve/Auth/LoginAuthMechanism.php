<?php

namespace PhpSieveManager\ManageSieve\Auth;

use PhpSieveManager\ManageSieve\SieveCommand;

class LoginAuthMechanism extends BaseAuthMechanism
{
    /**
     * SASL Authentication
     *
     * @return SieveCommand
     */
    function parse()
    {
        $extraLines = [
            base64_encode($this->username),
            base64_encode($this->password)
        ];
        return new SieveCommand(
            "AUTHENTICATE",
            ['"LOGIN"', false, $extraLines]
        );
    }
}