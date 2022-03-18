<?php

namespace PhpSieveManager\Filters;

class Condition
{
    private $description = "";
    private $criterias = [];

    /**
     * @param string $description
     */
    public function __construct(string $description = "")
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
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
            $parsed_str .= "# ".$this->description;
        }
        foreach ($this->criterias as $idx => $criteria) {
            $parsed_str .= $criteria->parse($idx);
        }
        return $parsed_str;
    }
}