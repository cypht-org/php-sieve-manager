<?php

namespace PhpSieveManager\ManageSieve\Auth;

use PhpSieveManager\ManageSieve\SieveCommand;

class Xoauth2AuthMechanism extends BaseAuthMechanism
{
    /**
     * SASL Authentication
     *
     * @return SieveCommand
     */
    function parse()
    {
        $args = base64_encode("user={$this->username}\001auth={$this->password}\001\001");
        return new SieveCommand(
            "AUTHENTICATE",
            ['"XOAUTH2"', '"'.$args.'"']
        );
    }
}