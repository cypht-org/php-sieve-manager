<?php

namespace PhpSieveManager\Filters\Actions;

use PhpSieveManager\Exceptions\FilterActionParamException;

/**
 * Please refer to https://datatracker.ietf.org/doc/html/rfc5293#section-5
 */
class DeleteHeaderFilterAction implements FilterAction
{
    private $params;

    /**
     * @param array $params
     * @throws FilterActionParamException
     */
    public function __construct(array $params = []) {
        if (count($params) != 1) {
            throw new FilterActionParamException("DeleteHeaderFilterAction expect one parameters");
        }
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function parse() {
        return 'deleteheader "'.$this->params[0].'";'."\n";
    }
}