<?php

namespace PhpSieveManager\Filters\Actions;

interface FilterAction
{
    public function __construct(array $params = []);
    public function parse();
}