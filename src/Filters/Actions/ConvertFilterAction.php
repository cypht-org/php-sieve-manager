<?php

namespace PhpSieveManager\Filters\Actions;

/**
 * Please refer to https://www.rfc-editor.org/rfc/rfc6558.html
 */
class ConvertFilterAction implements FilterAction
{
    private $fromMediaType;
    private $toMediaType;
    private $transcodingParams;

    public $require = ['convert'];

    /**
     * @param string $fromMediaType - The source media type
     * @param string $toMediaType - The target media type
     * @param array $transcodingParams - Parameters for transcoding
     */
    public function __construct($fromMediaType, $toMediaType, array $transcodingParams) {
        $this->fromMediaType = $fromMediaType;
        $this->toMediaType = $toMediaType;
        $this->transcodingParams = $transcodingParams;
    }

    /**
     * @return string
     */
    public function parse() {
        return "convert \"{$this->fromMediaType}\" \"{$this->toMediaType}\" [" . implode(', ', array_map(function($param) { return "\"$param\""; }, $this->transcodingParams)) . "];\n";
    }
}