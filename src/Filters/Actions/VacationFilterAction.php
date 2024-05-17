<?php

namespace PhpSieveManager\Filters\Actions;

class VacationFilterAction implements FilterAction
{
    private $days;
    private $subject;
    private $from;
    private $addresses;
    private $mime;
    private $handle;
    private $reason;
    private $seconds;

    public $require = ['vacation'];

    /**
     * @param string $reason - The reason for the vacation
     * @param int|null $days - The number of days
     * @param string|null $subject - The subject of the message
     * @param string|null $from - The from address
     * @param array|null $addresses - List of addresses
     * @param string|null $mime - Mime flag
     * @param string|null $handle - Handle
     */
    public function __construct($reason, $days = null, $seconds = null, $subject = null, $from = null, $addresses = [], $mime = null, $handle = null) {
        $this->days = $days;
        $this->subject = $subject;
        $this->from = $from;
        $this->addresses = $addresses;
        $this->mime = $mime;
        $this->handle = $handle;
        $this->reason = $reason;
        $this->seconds = $seconds;
        if ($seconds) {
            $this->require[] = 'vacation-seconds';
        }
    }

    /**
     * @return string
     */
    public function parse() {
        $script = "vacation";
        if ($this->seconds) {
            $script .= " :seconds {$this->days}";
        } elseif ($this->days) {
            $script .= " :days {$this->days}";
        }
        if ($this->subject) {
            $script .= " :subject \"{$this->subject}\"";
        }
        if ($this->from) {
            $script .= " :from \"{$this->from}\"";
        }
        if ($this->addresses) {
            $script .= " :addresses [\"" . implode('", "', $this->addresses) . "\"]";
        }
        if ($this->mime) {
            $script .= " :mime {$this->mime}";
        }
        if ($this->handle) {
            $script .= " :handle \"{$this->handle}\"";
        }
        $script .= " \"{$this->reason}\";\n";
        return $script;
    }
}