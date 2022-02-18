<?php

namespace ManageSieve\Auth;

use PhpSieveManager\ManageSieve\Auth\BaseAuthMechanism;
use PhpSieveManager\ManageSieve\Auth\Utils\DigestMD5;
use PhpSieveManager\ManageSieve\SieveCommand;

class DigestMd5AuthMechanism extends BaseAuthMechanism
{

    /**
     * @return SieveCommand
     */
    function parse()
    {
        $return = $this->client->sendCommand(
            'AUTHENTICATE',
            ['"DIGEST-MD5"'],
            true,
            null,
            1
        );

        $dmd5 = new DigestMD5(
            $return['response'],
            "sieve/".$this->client->getServerAddr()
        );
    }
}