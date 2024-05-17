<?php

namespace PhpSieveManager\Filters\Actions;

use PhpSieveManager\Exceptions\FilterActionParamException;

/**
 * Please refer to https://datatracker.ietf.org/doc/html/rfc5293#section-4
 */
class AddHeaderFilterAction implements FilterAction
{
    private $params;

    /**
     * @param array $params
     * @throws FilterActionParamException
     */
    public function __construct(array $params = []) {
        if (count($params) != 2) {
            throw new FilterActionParamException("AddHeaderFilterAction expect two parameters");
        }
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function parse() {
        return 'addheader "'.$this->params[0].'" "'.$this->params[1].'";'."\n";
    }
}