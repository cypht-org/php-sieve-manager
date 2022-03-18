<?php

namespace PhpSieveManager\Filters;

class FilterCriteria
{
    /**
     * @var string
     */
    private $target_field;

    /**
     * @var string
     */
    private $actions = [];

    /**
     * @var array
     */
    private $subcriterias = [];

    /**
     * @var string
     */
    private $comparator;

    /**
     * @var string
     */
    private $value;

    /**
     * Factory
     */
    private function __construct(string $target_field = "") {
        $this->target_field = $target_field;
    }

    /**
     * @param string $target_field
     * @return FilterCriteria
     */
    public static function if(string $target_field = ""): FilterCriteria
    {
        return new FilterCriteria($target_field);
    }

    /**
     * @param string $value
     * @return FilterCriteria
     */
    public function over(string $value) {
        $this->comparator = ':over';
        $this->value = $value;
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function is($value, ... $params) {
        $this->comparator = ':is';

        if (is_array($value)) {
            $this->value = '[';
            foreach ($value as $idx => $v) {
                $this->value .= '"'.$v.'"';
                if ($idx-1 != count($value)) {
                    $this->value .= ',';
                }
            }
            $this->value .= "]";
            $this->value .= " ".implode(' ', $params);
            return $this;
        }
        $this->value = $value;
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function matches(string $value) {
        $this->comparator = ':matches';
        $this->value = $value;
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function contains(string $value) {
        $this->comparator = ':contains';
        $this->value = $value;
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function under(string $value) {
        $this->comparator = ':under';
        $this->value = $value;
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function count(string $value) {
        $this->comparator = ':count';
        $this->value = $value;
        return $this;
    }

    /**
     * @param FilterCriteria $value
     * @return FilterCriteria
     */
    public function and(FilterCriteria $value) {
        return $this;
    }

    /**
     * @param FilterCriteria $value
     * @return FilterCriteria
     */
    public function or(FilterCriteria $value) {
        return $this;
    }

    /**
     * @param FilterCriteria $criteria
     * @return $this
     */
    public function addCriteria(FilterCriteria $criteria)
    {
        $this->subcriterias[] = $criteria;
        return $this;
    }

    /**
     * @param $action
     * @return FilterCriteria
     */
    public function addAction($action)
    {
        $this->actions[] = $action;
        return $this;
    }

    /**
     * @return string
     */
    public function parse($index = 0)
    {
        $parsed_str = "";
        if ($index == 0) {
            $parsed_str .= "\nif ".$this->target_field." ".$this->comparator." ".$this->value. " {";
        } else {
            if ($this->target_field != "") {
                $parsed_str .= "\nelsif ".$this->comparator." ".$this->value. " {";
            } else {
                $parsed_str .= "\nelse {";
            }
        }
        foreach ($this->subcriterias as $idx => $subcriteria) {
            $parsed_str .= $subcriteria->parse($idx);
        }

        foreach ($this->actions as $action) {
            $parsed_str .= "\n\t".$action->parse();
        }
        $parsed_str .= "\n}";
        return $parsed_str;
    }
}