<?php

namespace PhpSieveManager\Filters\Actions;

use PhpSieveManager\Exceptions\FilterActionParamException;

class FileIntoFilterAction implements FilterAction
{
    private $params;

    /**
     * @param array $params
     * @throws FilterActionParamException
     */
    public function __construct(array $params = []) {
        if (count($params) != 1) {
            throw new FilterActionParamException("FileIntoFilterAction expect one parameter");
        }
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function parse() {
        return 'fileinto "'.$this->params[0].'";'."\n";
    }
}