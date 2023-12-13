<?php

namespace PhpSieveManager\ManageSieve\Auth;

use PhpSieveManager\ManageSieve\SieveCommand;

class OauthbearerAuthMechanism extends BaseAuthMechanism
{
    /**
     * SASL Authentication
     *
     * @return SieveCommand
     */
    function parse()
    {
        $args = base64_encode("n,a={$this->username}\001auth=$this->password\001\001");
        return new SieveCommand(
            "AUTHENTICATE",
            ['"OAUTHBEARER"', '"'.$args.'"']
        );
    }
}