<?php

namespace PhpSieveManager\Filters\Actions;

use PhpSieveManager\Exceptions\FilterActionParamException;

/**
 * Please refer to https://www.rfc-editor.org/rfc/rfc5703.html#page-11
 */
class ExtractTextFilterAction implements FilterAction
{
    private $params;

    /**
     * @param array $params
     * @throws FilterActionParamException
     */
    public function __construct(array $params = []) {
        if ($params && count($params) > 2) {
            throw new FilterActionParamException("ExtractTextFilterAction expect one or two parameters");
        }
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function parse() {
        if (count($this->params) == 1) {
            return 'extracttext "'.$this->params[0].'";'."\n";
        }
        return 'extracttext :first '.$this->params[0].' "'.$this->params[1].'";'."\n";
    }
}