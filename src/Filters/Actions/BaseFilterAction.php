<?php

namespace PhpSieveManager\Filters\Actions;

use PhpSieveManager\Exceptions\FilterActionParamException;

abstract class BaseSieveAction implements FilterAction {
    protected array $params;

    public function __construct(array $params = []) {
        $this->params = $params;
        $this->validateParams();
    }

    protected function validateParams() {
        foreach ($this->getRequiredParams() as $param) {
            if (!isset($this->params[$param])) {
                throw new FilterActionParamException("Missing required parameter: $param");
            }
        }
        $this->validateTypes();
    }

    protected function validateTypes() {
        $paramTypes = $this->getParamTypes();
        foreach ($this->params as $key => $value) {
            if (isset($paramTypes[$key]) && !$this->isValidType($key, $value)) {
                throw new FilterActionParamException("Invalid type for parameter: $key. Expected " . $value);
            }
        }
    }

    /**
     * @param mixed $value
     * @param string $type
     * 
     * @return bool
     */
    protected function isValidType($value, $type) {
        switch ($type) {
            case 'string':
                return is_string($value);
            case 'int':
                return is_int($value);
            case 'float':
                return is_float($value);
            case 'bool':
                return is_bool($value);
            case 'array':
                return is_array($value);
            case 'string-list':
                return is_array($value) && array_reduce($value, function($carry, $item) { return $carry && is_string($item); }, true);
            default:
                return false;
        }
    }

    /**
     * @return array
     */
    abstract protected function getRequiredParams();

    /**
     * @return array
     */
    abstract protected function getParamTypes();
}