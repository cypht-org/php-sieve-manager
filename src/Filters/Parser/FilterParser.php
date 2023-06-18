<?php

namespace PhpSieveManager\Filters\Parser;

use PhpSieveManager\Filters\Condition;

class FilterParser
{
    private $requirements = [];

    /**
     * @var Condition
     */
    private $conditions;

    /**
     * @param array $requirements
     * @param Condition $conditions
     */
    public function __construct(array $requirements, array $conditions) {
        $this->requirements = $requirements;
        $this->conditions = $conditions;
    }

    /**
     * @param array $requirements
     * @param Condition $conditions
     * @return string
     */
    public static function parseFromConditions(array $requirements, array $conditions)
    {
        $parser = new self($requirements, $conditions);
        return $parser->parse();
    }

    /**
     * @return string
     */
    private function parseRequirements() {
        if (!count($this->requirements)) {
            return "";
        }

        $parsed = "# Requirements\n";
        $reqs = [];
        foreach ($this->requirements as $req) {
            $reqs[] = '"'.$req.'"';
        }
        $parsed .= 'require ['.implode(',', $reqs).'];'."\n";
        return $parsed;
    }

    /**
     * @return string
     */
    private function parseConditions() {
        $ret = '';
        foreach ($this->conditions as $key => $condition) {
            $ret .= $condition->parse($key == 0);
        }
        return $ret;
    }

    /**
     * @return string
     */
    public function parse() {
        $parsed = $this->parseRequirements();
        $parsed .= $this->parseConditions();
        return $parsed;
    }
}