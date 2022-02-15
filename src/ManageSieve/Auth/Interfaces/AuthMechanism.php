<?php

namespace PhpSieveManager\ManageSieve\Auth\Interfaces;

interface AuthMechanism
{
    function __construct($username, $password, $authz_id="");
    function parse();
}