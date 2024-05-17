<?php

namespace PhpSieveManager\Filters\Actions;

use PhpSieveManager\Exceptions\FilterActionParamException;

/**
 * Please refer to https://www.rfc-editor.org/rfc/rfc5229.html
 */
class SetFilterAction implements FilterAction
{
    private $params;

    /**
     * @param array $params
     * @throws FilterActionParamException
     */
    public function __construct(array $params = []) {
        if (count($params) != 2) {
            throw new FilterActionParamException("SetFilterAction expect two parameters");
        }
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function parse() {
        return 'set "'.$this->params[0].'" "'.$this->params[1].'";'."\n";
    }
}