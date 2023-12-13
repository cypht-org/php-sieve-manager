<?php

namespace PhpSieveManager\ManageSieve\Auth;

use PhpSieveManager\ManageSieve\SieveCommand;

class ExternalAuthMechanism extends BaseAuthMechanism
{
    /**
     * SASL Authentication
     *
     * @return SieveCommand
     */
    function parse()
    {
        $args = base64_encode($this->username);
        return new SieveCommand(
            "AUTHENTICATE",
            ['"EXTERNAL"', '"'.$args.'"']
        );
    }
}