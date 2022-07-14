<?php

namespace PhpSieveManager\Filters\Actions;

class AddFlagFilterAction implements FilterAction
{
    private $params;

    /**
     * @param array $params
     * @throws FilterActionParamException
     */
    public function __construct(array $params = []) {
        if (count($params) != 1) {
            throw new FilterActionParamException("AddFlagAction expect one parameter");
        }
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function parse() {
        return 'addflag "'.$this->params[0].'";'."\n";
    }
}