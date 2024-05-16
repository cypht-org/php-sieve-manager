<?php

namespace PhpSieveManager\Filters\Actions;

use PhpSieveManager\Exceptions\FilterActionParamException;

/**
 * Please refer to https://www.rfc-editor.org/rfc/rfc5703.html#section-6
 */
class EncloseFilterAction implements FilterAction
{
    private $params;

    /**
     * @param array $params
     * @throws FilterActionParamException
     */
    public function __construct(array $params = []) {
        if (count($params) != 1) {
            throw new FilterActionParamException("EncloseFilterAction expect one parameters");
        }
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function parse() {
        return 'enclose "'.$this->params[0].'";'."\n";
    }
}