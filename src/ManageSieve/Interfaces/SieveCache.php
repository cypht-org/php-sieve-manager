<?php

namespace PhpSieveManager\ManageSieve\Interfaces;

interface SieveCache
{
    public function get(string $key);
    public function set(string $key, $value, int $ttl): bool;
    public function delete(string $key): bool;
}
