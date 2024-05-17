<?php

namespace PhpSieveManager\Filters\Actions;

/**
 * Please refer to https://datatracker.ietf.org/doc/rfc5435/
 */
class NotifyFilterAction implements FilterAction
{
    private $from;
    private $importance;
    private $options;
    private $message;
    private $method;

    public $require = ['enotify'];

    /**
     * @param string $method - The notification method
     * @param string|null $from - The sender of the notification
     * @param int|null $importance - The importance level (optional, values: "1", "2", "3")
     * @param array|null $options - Additional options for notification
     * @param string|null $message - The notification message
     */
    public function __construct($method, $from = null, $importance = null, $options = [], $message = null) {
        $this->method = $method;
        $this->from = $from;
        if ($importance < 1 || $importance > 3) {
            $importance = 2;
        }
        $this->importance = $importance;
        $this->options = $options;
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function parse() {
        $script = "notify";
        if ($this->from) {
            $script .= " :from \"{$this->from}\"";
        }
        if ($this->importance) {
            $script .= " :importance \"{$this->importance}\"";
        }
        if ($this->options) {
            $script .= " :options [\"" . implode('", "', $this->options) . "\"]";
        }
        if ($this->message) {
            $script .= " :message \"{$this->message}\"";
        }
        $script .= " \"{$this->method}\";\n";
        return $script;
    }
}