<?php

namespace PhpSieveManager\Filters\Actions;

use PhpSieveManager\Exceptions\FilterActionParamException;

class VacationFilterAction implements FilterAction
{
    private $params;

    /**
     * @param array $params
     * @throws FilterActionParamException
     */
    public function __construct(array $params = []) {
        if (count($params) < 1 || count($params) > 2) {
            throw new FilterActionParamException("VacationFilterAction at least one parameter");
        }
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function parse() {
        if (count($this->params) == 2) {
            return 'vacation :subject "' . $this->params[0] . '" "' . $this->params[1] . '";';
        }
        return 'vacation "' . $this->params[0] . '";';
    }
}