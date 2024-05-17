<?php

namespace PhpSieveManager\Filters\Actions;

class FlagFilterAction extends BaseFlagFilterAction
{
    public function getScriptName()
    {
        return 'setflag';
    }
}