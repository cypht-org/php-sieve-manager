<?php

namespace PhpSieveManager\Filters;

class Condition
{
    private $description = "";
    private $criterias = [];
    private $test_list = 'anyof';
    private $requirements = [];

    /**
     * @var string
     */
    private $actions = [];

    /**
     * @param string $description
     */
    public function __construct(string $description = "", $test_list='anyof')
    {
        $this->description = $description;
        $this->test_list = $test_list;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param $action
     * @return Condition
     */
    public function addAction($action)
    {
        if (isset($action->require)) {
            $this->requirements[] = $action->require;
        }
        $this->actions[] = $action;
        return $this;
    }

    /**
     * @return array
     */
    public function getRequirements() {
        return $this->requirements;
    }

    /**
     * @param FilterCriteria $criteria
     * @return $this;
     */
    public function addCriteria(FilterCriteria $criteria)
    {
        $this->criterias[] = $criteria;
        return $this;
    }

    /**
     * @return string
     */
    public function parse()
    {
        $parsed_str = "\n";
        if ($this->description != "") {
            $parsed_str .= "# ".$this->description. "\n";
        }

        $parsed_str .= 'if ';
        if (count($this->criterias) > 1) {
            $parsed_str .= $this->test_list.'(';
        }

        foreach ($this->criterias as $idx => $criteria) {
            if ($idx != 0) {
                $parsed_str .= ' ,'.$this->comparator_type.' ';
            }
            $parsed_str .= $criteria->parse($idx);
        }
        if (count($this->criterias) > 1) {
            $parsed_str .= ')';
        }
        $parsed_str .= ' {'."\n";
        foreach ($this->actions as $action) {
            $parsed_str .= "\t".$action->parse();
        }
        $parsed_str .= "\n".'}';
        return $parsed_str;
    }
}