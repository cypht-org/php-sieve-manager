<?php

namespace PhpSieveManager\Filters\Actions;

use PhpSieveManager\Exceptions\FilterActionParamException;

/**
 * Please refer to https://www.rfc-editor.org/rfc/rfc6558.html
 */
class ConvertFilterAction implements FilterAction
{
    private $params;

    /**
     * @param array $params
     * @throws FilterActionParamException
     */
    public function __construct(array $params = []) {
        if (count($params) != 3) {
            throw new FilterActionParamException("ConvertFilterAction expect three parameters");
        }
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function parse() {
        return 'convert "'.$this->params[0].'" "'.$this->params[1].'" ["'.implode('","', $this->params[2]).'"];'."\n";
    }
}