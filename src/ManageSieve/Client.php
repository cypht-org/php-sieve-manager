<?php

namespace PhpSieveManager\ManageSieve;

use PhpSieveManager\Exceptions\ResponseException;
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
        $this->respCodeExpression = "#(OK|NO|BYE)\s*(.+)?#";
        $this->errorCodeExpression = '#(\([\w/-]+\))?\s*(".+")#';
        $this->sizeExpression = "#\{(\d+)\+?\}#";
        $this->activeExpression = "#ACTIVE#";
    }

    /**
     * Read line from the server
     * @return false|string
     * @throws SocketException
     * @throws LiteralException
     * @throws ResponseException
     */
    private function readLine() {
        $return = "";
        while (true) {
            try {
                if ($this->readBuffer != null) {
                    $pos = strpos($this->readBuffer, "\r\n");
                    $return = substr($this->readBuffer, 0, $pos);
                    $this->readBuffer = substr($this->readBuffer, $pos + strlen("\r\n"));
                    break;
                }
            } catch (\Exception $e) {
                $this->debugPrint($e->getMessage());
            }

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
                if (strstr($matches[0], "NOTIFY")) {
                    return $return;
                }
                switch ($matches[1]) {
                    case "BYE":
                        throw new SocketException("Connection closed by the server");
                    case "NO":
                        $this->parseError($matches[2]);
                }
                throw new ResponseException($matches[1], $matches[2]);
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
     * @return mixed
     */
    public function getErrorMessage() {
        return $this->errorMessage;
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
            $this->errorCode = trim($matches[1], '()');
        } else {
            $this->errorCode = "";
        }
        $this->errorMessage = trim($matches[2], '"');
    }

    /**
     * @return void
     * @throws LiteralException
     * @throws ResponseException
     * @throws SocketException
     */
    public function logout() {
        $this->sendCommand("LOGOUT");
    }

    /**
     * @return string|null
     * @throws LiteralException
     * @throws ResponseException
     * @throws SocketException
     */
    public function capability() {
        $return_payload = $this->sendCommand("CAPABILITY", null, true);
        if ($return_payload['code'] == 'OK') {
            return $return_payload['response'];
        }
        return null;
    }

    /**
     * @param int $num_lines
     * @return array
     * @throws LiteralException
     * @throws ResponseException
     * @throws SocketException
     */
    private function readResponse($num_lines = -1) {
        $response = "";
        $code = null;
        $data = null;
        $cpt = 0;

        while (true) {
            try {
                $line = $this->readLine();
            } catch (ResponseException $e) {
                $code = $e->code;
                $data = $e->data;
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

    private function debugPrint($message) {
        if ($this->debug) {
            echo ("[DEBUG][".date("Y-m-d H:i:s")."] " . $message. "\n");
        }
    }

    /**
     * Send a command to the server.
     *
     * @param $name
     * @param $args
     * @param bool $withResponse
     * @param $extralines
     * @param int $numLines
     * @return string[]
     * @throws LiteralException
     * @throws ResponseException
     * @throws SocketException
     */
    private function sendCommand($name, $args=null, $withResponse=false, $extralines=null, $numLines=-1) {
        $command = $name;
        if ($args != null) {
            $command .= ' ';
            $command .= implode(' ', $args);
        }

        $command = $command."\r\n";

        $this->debugPrint($command);
        socket_write($this->sock, $command, strlen($command));

        if ($extralines) {
            foreach ($extralines as $line) {
                socket_write($this->sock, $line, strlen($line));
            }
        }
        $response_payload = $this->readResponse($numLines);

        if ($withResponse) {
            return [
                "code" => $response_payload["code"],
                "data" => $response_payload["data"],
                "response" => $response_payload["response"]
            ];
        }

        return [
            "code" => $response_payload["code"],
            "data" => $response_payload["data"]
        ];
    }

    /**
     * Format script before send to server
     *
     * @param $content
     * @return string
     */
    private function prepareContent($content) {
        return "{".strlen($content)."}"."\r\n".$content;
    }

    /**
     * Upload script
     *
     * @param $name
     * @param $content
     * @return bool
     * @throws LiteralException
     * @throws ResponseException
     * @throws SocketException
     */
    public function putScript($name, $content) {
        $content = $this->prepareContent($content);
        $return_payload = $this->sendCommand("PUTSCRIPT", [$name, $content]);
        if ($return_payload["code"] == "OK") {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     * @throws LiteralException
     * @throws ResponseException
     * @throws SocketException
     */
    private function getCapabilitiesFromServer() {
        $payload = $this->readResponse();
        if ($payload["code"] == "NO") {
            return false;
        }
        foreach (explode("\r\n", $payload["response"]) as $l) {
            $parts = explode(" ", $l, 2);
            $cname = trim($parts[0], '"');
            if (!in_array($cname, $this::KNOWN_CAPABILITIES)) {
                continue;
            }
            $this->capabilities[$cname] = null;
            if (count($parts) > 1) {
                $this->capabilities[$cname] = trim($parts[1], '"');
            }
        }
        return true;
    }

    /**
     * @return mixed
     */
    public function getCapabilities() {
        return $this->capabilities;
    }

    /**
     * @param string $username
     * @param string $password
     * @param bool $tls
     * @return void
     * @throws LiteralException
     * @throws ResponseException
     * @throws SocketException
     */
    public function connect($username="", $password="", $tls=false) {
        if (($this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
            throw new SocketException("Socket creation failed: " . socket_strerror(socket_last_error()));
        }
        socket_set_option($this->sock,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>$this->readTimeout, "usec"=>0));

        if(($result = socket_connect($this->sock, $this->addr, $this->port)) === false) {
            throw new SocketException("Socket connect failed: " . socket_strerror(socket_last_error($this->sock)));
        }
        $this->connected = true;
        if (!$this->getCapabilitiesFromServer()) {
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