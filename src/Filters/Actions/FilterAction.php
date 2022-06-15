<?php

namespace PhpSieveManager\Filters\Actions;

interface FilterAction
{
    public function parse();
    public function __construct(array $params = []);
}