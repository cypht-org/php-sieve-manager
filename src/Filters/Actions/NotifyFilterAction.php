<?php

namespace PhpSieveManager\Filters\Actions;

use PhpSieveManager\Exceptions\FilterActionParamException;

/**
 * Please refer to https://datatracker.ietf.org/doc/rfc5435/
 */
class NotifyFilterAction implements FilterAction
{
    private $params;

    /**
     * @param array $params
     * @throws FilterActionParamException
     */
    public function __construct(array $params = []) {
        if (count($params) != 3) {
            throw new FilterActionParamException("NotifyFilterAction expect three parameters");
        }
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function parse() {
        return 'notify :importance "'.$this->params[0].'" :text "'.$this->params[1].'" "'.$this->params[2].'";'."\n";
    }
}