<?php

namespace PhpSieveManager\ManageSieve\Interfaces;

interface SieveClient
{
    function putScript($name, $content);
    function logout();
    function getSASLMechanisms();
    function getErrorMessage();
    function getCapabilities();
    function connect($username, $password, $tls=false, $authz_id="", $auth_mechanism=null);
    function close();
    function capability();

}