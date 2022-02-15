<?php

namespace PhpSieveManager\ManageSieve\Interfaces;

interface SieveClient
{
    function putScript($name, $content);
    function logout();
    function getSASLMechanism();
    function getErrorMessage();
    function getCapabilities();
    function connect($username="", $password="", $tls=false);
    function close();
    function capability();

}