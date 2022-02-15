<?php

namespace PhpSieveManager\ManageSieve;

use PhpSieveManager\Exceptions\LiteralException;
use PhpSieveManager\Exceptions\SocketException;
use PhpSieveManager\ManageSieve\Interfaces\SieveClient;
use PhpSieveManager\Utils\StringUtils;

/**
 * ManageSieve Client
 */
class Client extends SieveClient
{
    const KNOWN_CAPABILITIES = ["IMPLEMENTATION", "SASL", "SIEVE", "STARTTLS", "NOTIFY", "LANGUAGE", "VERSION"];

    private $readSize = 4096;
    private $readTimeout = 5;

    private $errorMessage;
    private $readBuffer;
    private $capabilities;
    private $addr;
    private $port;
    private $debug;
    private $sock;
    private $authenticated;
    private $errorCode;
    private $connected = false;

    private $respCodeExpression;
    private $errorCodeExpression;
    private $sizeExpression;
    private $activeExpression;

    /**
     * @param $addr
     * @param $port
     * @param $debug
     */
    public function __construct($addr, $port=4190, $debug=false) {
        $this->addr = $addr;
        $this->port = $port;
        $this->debug = $debug;
        $this->initExpressions();
    }

    /**
     * @return void
     */
    private function initExpressions() {
        $this->respCodeExpression = "(OK|NO|BYE)\s*(.+)?";
        $this->errorCodeExpression = '(\([\w/-]+\))?\s*(".+")';
        $this->sizeExpression = "\{(\d+)\+?\}";
        $this->activeExpression = "ACTIVE";
    }

    /**
     * Read line from the server
     * @return false|string
     * @throws SocketException
     * @throws LiteralException
     */
    private function readLine() {
        $return = "";
        while (true) {
            try {
                $pos = strpos($this->readBuffer, "\r\n");
                $return = substr($this->readBuffer, 0, $pos);
                $this->readBuffer = $this->readBuffer[$pos + strlen("\r\n")];
                break;
            } catch (\Exception $e) { }

            try {
                $nval = socket_read($this->sock, $this->readSize);
                if ($nval === false) {
                    break;
                }
                $this->readBuffer .= $nval;
            } catch (\Exception $e) {
                throw new SocketException("Failed to read data from the server.");
            }
        }

        if (strlen($return)) {
            preg_match($this->sizeExpression, $return, $matches);
            if ($matches) {
                throw new LiteralException($matches[1]);
            }

            preg_match($this->respCodeExpression, $return, $matches);
            if ($matches) {
                switch ($matches[1]) {
                    case "BYE":
                        throw new SocketException("Connection closed by the server");
                    case "NO":
                        $this->parseError($matches[2]);
                }
                throw new SocketException($matches[1] . ' ' . $matches[2]);
            }
        }

        return $return;
    }

    /**
     * Read a block of $size bytes from the server.
     *
     * @param $size
     * @return false|string
     * @throws SocketException
     */
    private function readBlock($size) {
        $buffer = "";
        if (count($this->readBuffer)) {
            $limit = count($this->readBuffer);
            if ($size <= count($this->readBuffer)) {
                $limit = $size;
            }

            $buffer = substr($this->readBuffer, 0, $limit);
            $this->readBuffer = substr($this->readBuffer, $limit);
            $size -= $limit;
        }

        if (!isset($size)) {
            return $buffer;
        }

        try {
            $buffer .= socket_read($this->sock, $size);
        } catch (\Exception $e) {
            throw new SocketException("Failed to read from the server");
        }

        return $buffer;
    }

    /**
     * Parse errors received from the server
     *
     * @return void
     * @throws SocketException
     */
    private function parseError($text) {
        preg_match($this->sizeExpression, $text, $matches);
        if ($matches) {
            $this->errorCode = "";
            $this->errorMessage = $this->readBlock($matches[1] + 2);
            return;
        }

        preg_match($this->errorCodeExpression, $text, $matches);
        if ($matches == false || count($matches) == 0) {
            throw new SocketException("Bad error message");
        }

        if (array_key_exists(1, $matches)) {
            $this->errorCode = trim($matches[1], ['(', ')']);
        } else {
            $this->errorCode = "";
        }
        $this->errorMessage = trim($matches[2], ['"']);
    }

    /**
     * @param $num_lines
     * @return array
     */
    private function readResponse($num_lines = -1) {
        $response = "";
        $code = null;
        $data = null;
        $cpt = 0;

        while (true) {
            try {
                $line = $this->readLine();
            } catch (SocketException $e) {
                $code = $e->getCode();
                $data = $e->getMessage();
                break;
            } catch (LiteralException $e) {
                $response .= $this->readBlock($e->getMessage());
                if (StringUtils::endsWith($response, "\r\n")) {
                    $response .= $this->readLine() . "\r\n";
                }
                continue;
            }

            if (!strlen($line)) {
                continue;
            }

            $response .= $line . "\r\n";
            $cpt += 1;
            if ($num_lines != -1 && $cpt == $num_lines) {
                break;
            }
        }

        return [
            "code" => $code,
            "data" => $data,
            "response" => $response
        ];
    }

    /**
     * @return bool
     */
    private function getCapabilities() {
        $payload = $this->readResponse();
        if ($payload["code"] == "NO") {
            return false;
        }

        foreach (explode("\n", $payload["response"]) as $l) {
            $parts = explode(" ", $l, 1);
            $cname = trim($parts[0], ['"']);
            if (!in_array($cname, $this::KNOWN_CAPABILITIES)) {
                continue;
            }

            $this->capabilities[$cname] = null;
            if (count($parts) > 1) {
                $this->capabilities[$cname] = trim($parts[1], ['"']);
            }
        }
        return true;
    }

    /**
     * @param $username
     * @param $password
     * @param bool $tls
     * @return void
     * @throws SocketException
     */
    public function connect($username, $password, $tls=false) {
        if (($this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
            throw new SocketException("Socket creation failed: " . socket_strerror(socket_last_error()));
        }

        if(($result = socket_connect($this->sock, $this->addr, $this->port)) === false) {
            throw new SocketException("Socket connect failed: (".$result.") " . socket_strerror(socket_last_error($this->sock)));
        }
        $this->connected = true;

        if (!$this->getCapabilities()) {
            throw new SocketException("Failed to read capabilities from the server");
        }
    }

    /**
     * @return void
     */
    public function close() {
        socket_close($this->sock);
    }

    public function __destruct()
    {
        if ($this->connected) {
            $this->close();
        }
    }

}