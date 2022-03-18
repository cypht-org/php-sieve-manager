<?php
namespace PhpSieveManager\Filters;

use PhpSieveManager\Filters\Parser\FilterParser;

class FilterFactory
{
    /**
     * @var string
     */
    private $name;
    private $require = [];

    /**
     * @var Condition
     */
    private $conditions;

    /**
     * @param $name string Script Name
     */
    private function __construct($name)
    {
    }

    /**
     * @param $name string
     * @return FilterFactory
     */
    public static function create(string $name)
    {
        return new FilterFactory($name);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $requirement string
     * @return $this
     */
    public function addRequirement(string $requirement): FilterFactory
    {
        $this->require[] = $requirement;
        return $this;
    }


    /**
     * @return $this
     */
    public function setCondition(Condition $condition): FilterFactory
    {
        $this->conditions = $condition;
        return $this;
    }

    /**
     * @return string
     */
    public function toScript()
    {
        return FilterParser::parseFromConditions($this->require, $this->conditions);
    }
}