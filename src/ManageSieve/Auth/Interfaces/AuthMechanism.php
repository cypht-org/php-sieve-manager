<?php

namespace PhpSieveManager\ManageSieve\Auth\Interfaces;

use PhpSieveManager\ManageSieve\Client;
use PhpSieveManager\ManageSieve\SieveCommand;

interface AuthMechanism
{
    /**
     * @param $username
     * @param $password
     * @param $client Client
     * @param $authz_id
     */
    function __construct($username, $password, $client, $authz_id="");

    /**
     * @return SieveCommand
     */
    function parse();
}