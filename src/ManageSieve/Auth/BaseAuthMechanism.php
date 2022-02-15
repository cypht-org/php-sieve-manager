<?php

namespace PhpSieveManager\ManageSieve\Auth;

use PhpSieveManager\ManageSieve\Auth\Interfaces\AuthMechanism;

abstract class BaseAuthMechanism implements AuthMechanism
{
    protected $username;
    protected $password;
    protected $authz_id;

    /**
     * @param $username
     * @param $password
     * @param $authz_id
     */
    public function __construct($username, $password, $authz_id="") {
        $this->username = $username;
        $this->password = $password;
        $this->authz_id = $authz_id;
    }
}