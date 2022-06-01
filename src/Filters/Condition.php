<?php

namespace PhpSieveManager\Filters;

class Condition
{
    private $description = "";
    private $criterias = [];
    private $test_list = 'anyof';

    /**
     * @var string
     */
    private $actions = [];

    /**
     * @param string $description
     */
    public function __construct(string $description = "", FilterCriteria $first_criteria, $test_list='anyof')
    {
        $this->addCriteria($first_criteria);
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
        $this->actions[] = $action;
        return $this;
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