<?php

namespace PhpSieveManager\Filters\Actions;

class ErejectFilterAction extends BaseRejectFilterAction
{
    protected function getType()
    {
        return 'ereject';
    }
}