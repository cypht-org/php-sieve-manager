<?php

namespace PhpSieveManager\ManageSieve;

use PhpSieveManager\Exceptions\ResponseException;
use PhpSieveManager\Exceptions\LiteralException;
use PhpSieveManager\Exceptions\SieveException;
use PhpSieveManager\Exceptions\SocketException;
use PhpSieveManager\ManageSieve\Interfaces\SieveClient;
use PhpSieveManager\Utils\StringUtils;

/**
 * ManageSieve Client
 */
class Client implements SieveClient
{
    const SUPPORTED_AUTH_MECHS = ["DIGEST-MD5", "PLAIN", "LOGIN", "EXTERNAL", "OAUTHBEARER", "XOAUTH2"];
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
    private $authenticated = false;
    private $errorCode;
    private $connected = false;

    private $respCodeExpression;
    private $errorCodeExpression;
    private $sizeExpression;
    private $activeExpression;

    /**
     * Constructor
     *
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
     * Init regex expressions variables
     *
     * @return void
     */
    private function initExpressions() {
        $this->respCodeExpression = "#(OK|NO|BYE)\s*(.+)?#";
        $this->errorCodeExpression = '#(\([\w/-]+\))?\s*(".+")#';
        $this->sizeExpression = "#\{(\d+)\+?\}#";
        $this->activeExpression = "#ACTIVE#";
    }

    private function getSingleLine() {
        $pos = strpos($this->readBuffer, "\r\n");
        $return = substr($this->readBuffer, 0, $pos);
        return [$return, $pos];
    }

    /**
     * Read line from the server
     *
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
                    list($return, $pos) = $this->getSingleLine();
                    $this->readBuffer = substr($this->readBuffer, $pos + strlen("\r\n"));
                    break;
                }
            } catch (\Exception $e) {
                $this->debugPrint($e->getMessage());
            }

            try {
                $nval = \fread($this->sock, $this->readSize);
                if ($nval === false) {
                    break;
                }
                $this->readBuffer .= $nval;
            } catch (\Exception $e) {
                throw new SocketException("Failed to read data from the server.");
            }
        }

        if (strlen($return)) {
            preg_match($this->respCodeExpression, $return, $matches);
            if (count($matches)) {
                if (strpos($matches[0], "NOTIFY") !== false) {
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

            preg_match($this->sizeExpression, $return, $matches);
            if (count($matches)) {
                throw new LiteralException($matches[1]);
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
        if (strlen($this->readBuffer)) {
            $limit = strlen($this->readBuffer);
            if ($size <= strlen($this->readBuffer)) {
                $limit = $size;
            }

            $buffer = substr($this->readBuffer, 0, $limit);
            $this->readBuffer = substr($this->readBuffer, $limit);
            $size -= $limit;
        }

        if (! isset($size) || empty($size)) {
            return $buffer;
        }

        try {
            $buffer .= \fread($this->sock, $size);
        } catch (\Exception $e) {
            throw new SocketException("Failed to read from the server");
        }

        return $buffer;
    }

    /**
     * Get last error message retrieved from
     * the server.
     *
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
            $errorMessage = $matches[1] + 2;
            list($nextLine, $_) = $this->getSingleLine();
            if (preg_match('/^\d+$/', trim($errorMessage)) && preg_match('/error:/i', $nextLine)) {
                $this->errorMessage = $nextLine;
            } else {
                $this->errorMessage = $this->readBlock($errorMessage);
            }
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
     * LOGOUT
     *
     * @return void
     * @throws LiteralException
     * @throws ResponseException
     * @throws SocketException
     */
    public function logout() {
        $this->sendCommand("LOGOUT");
    }

    /**
     * CAPABILITY
     *
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
     * Read server response line per line
     *
     * @param int $num_lines
     * @return array
     * @throws LiteralException
     * @throws ResponseException
     * @throws SocketException
     */
    private function readResponse($num_lines = -1): array
    {
        $empty_return_count = 0;
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
                $empty_return_count = $empty_return_count + 1;
                if ($empty_return_count < 5) {
                    continue;
                }
                throw new SocketException("readResponse time out");
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
     * Debug messages if debug is enabled
     *
     * @param $message
     * @return void
     */
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
    public function sendCommand($name, $args=null, $withResponse=false, $extralines=null, $numLines=-1): array
    {
        $command = $name;
        if ($args != null) {
            $command .= ' ';
            $command .= implode(' ', $args);
        }

        $command = $command."\r\n";

        $this->debugPrint($command);
        \fwrite($this->sock, $command, strlen($command));

        if ($extralines) {
            foreach ($extralines as $line) {
                \fwrite($this->sock, $line."\r\n", strlen($line."\r\n"));
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
     * Return the supported authentication mechanism
     * from the server.
     *
     * @return false|string[]
     */
    public function getSASLMechanisms() {
        $available = $this->capabilities["SASL"];
        if (empty($available)) {
            return [];
        } else {
            return explode(" ", $available);
        }
    }

    /**
     * Authenticate to server
     *
     * @param $username
     * @param $password
     * @param string $authz_id
     * @param $auth_mechanism
     * @return bool
     * @throws LiteralException
     * @throws ResponseException
     * @throws SieveException
     * @throws SocketException
     */
    private function authenticate($username, $password, bool $tls = false, string $authz_id = "", $auth_mechanism = null): bool
    {
        if(array_key_exists("SASL", $this->capabilities) && $server_mechanisms = $this->getSASLMechanisms()) {
            // will filter by server_mechanisms
        } elseif (array_key_exists("STARTTLS", $this->capabilities) && $tls) {
            // start the TLS connection here
            $response = $this->sendCommand('STARTTLS');
            if ($response['code'] != 'OK') {
                throw new SieveException('Error starting TLS connection to ManageSieve server: ' . $response['data']);
            }
            if (function_exists('get_tls_stream_type')) {
                $type = get_tls_stream_type();
            } else {
                $type = STREAM_CRYPTO_METHOD_TLS_CLIENT;
                if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
                    $type |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
                    $type |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
                }
            }
            stream_socket_enable_crypto($this->sock, true, $type);
            $this->getCapabilitiesFromServer();
            $server_mechanisms = $this->getSASLMechanisms();
        } else {
            // continue without tls or sasl
            $server_mechanisms = [];
        }

        $mech_list = $this::SUPPORTED_AUTH_MECHS;
        if ($auth_mechanism != null && in_array($auth_mechanism, $this::SUPPORTED_AUTH_MECHS)) {
            $mech_list = [$auth_mechanism];
        }

        foreach ($mech_list as $mech) {
            if ($server_mechanisms && ! in_array($mech, $server_mechanisms)) {
                continue;
            }
            $mech = str_replace("-", "", strtolower($mech));
            $authentication_method = "PhpSieveManager\ManageSieve\Auth\\".ucfirst($mech)."AuthMechanism";
            $auth_mechanism_obj = new $authentication_method($username, $password, $this, $authz_id);
            $generated_command = $auth_mechanism_obj->parse();
            $return_payload = $this->sendCommand(
                $generated_command->name,
                $generated_command->args,
                $generated_command->withResponse,
                $generated_command->extralines,
                $generated_command->numLines
            );

            if ($return_payload['code'] == "OK") {
                $this->authenticated = true;
                return true;
            }
            return false;
        }

        $this->errorMessage = "No suitable mechanism found";
        return false;
    }

    /**
     * Format script before send to server
     *
     * @param $content
     * @return string
     */
    private function prepareContent($content): string
    {
        return "{".strlen($content)."+}"."\r\n".$content;
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
    public function putScript($name, $content): bool
    {
        $content = $this->prepareContent($content);
        $return_payload = $this->sendCommand("PUTSCRIPT", ['"'.$name.'"', $content]);
        if ($return_payload["code"] == "OK") {
            return true;
        }
        return false;
    }

    /**
     * Delete script
     *
     * @param string $name
     * @return bool
     * @throws LiteralException
     * @throws ResponseException
     * @throws SocketException
     */
    public function getScript(string $name)
    {
        $return_payload = $this->sendCommand("GETSCRIPT", ['"'.$name.'"'], true);
        if ($return_payload["code"] == "OK") {
            return $return_payload['response'];
        }
        return false;
    }

    /**
     * Activate script
     *
     * @param string $name
     * @return bool
     * @throws LiteralException
     * @throws ResponseException
     * @throws SocketException
     */
    public function activateScript(string $name): bool
    {
        $return_payload = $this->sendCommand("SETACTIVE", ['"'.$name.'"']);
        if ($return_payload["code"] == "OK") {
            return true;
        }
        return false;
    }

    /**
     * Delete script
     *
     * @param string $name
     * @return bool
     * @throws LiteralException
     * @throws ResponseException
     * @throws SocketException
     */
    public function removeScripts(string $name): bool
    {
        $return_payload = $this->sendCommand("DELETESCRIPT", ['"'.$name.'"']);
        if ($return_payload["code"] == "OK") {
            return true;
        }
        return false;
    }

    /**
     * Retrieve script
     *
     * @return bool|array
     * @throws LiteralException
     * @throws ResponseException
     * @throws SocketException
     */
    public function listScripts() {
        $return_payload = $this->sendCommand("LISTSCRIPTS", NULL, true);
        if ($return_payload["code"] == "OK") {
            $scripts = [];
            foreach (explode("\n", $return_payload['response']) as $script_name) {
                if (trim($script_name) != '') {
                    $scripts[] = str_replace('" ACTIV', '', substr($script_name, 1, -2));
                }
            }
            return $scripts;
        }
        return false;
    }

    /**
     * Get server capabilities
     *
     * @return bool
     * @throws LiteralException
     * @throws ResponseException
     * @throws SocketException
     */
    private function getCapabilitiesFromServer(): bool
    {
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
        $this->capabilities['extensions'] = preg_split('/\s+/', $this->capabilities['SIEVE']);
        return true;
    }

    /**
     * Get capabilities array
     *
     * @return mixed
     */
    public function getCapabilities() {
        return $this->capabilities;
    }

    /**
     * Connect to ManageSieve Protocol
     *
     * @param string $username
     * @param string $password
     * @param bool $tls
     * @return bool
     * @throws LiteralException
     * @throws ResponseException
     * @throws SocketException|SieveException
     */
    public function connect($username, $password, $tls=false, $authz_id="", $auth_mechanism=null) {
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'verify_peer_name', false);
        stream_context_set_option($ctx, 'ssl', 'verify_peer', false);
        $this->sock = stream_socket_client($this->addr.':'.$this->port, $errorno, $errorstr, $this->readTimeout, STREAM_CLIENT_CONNECT, $ctx);

        if ($this->sock === false) {
            throw new SocketException("Socket creation failed: " . $errorstr);
        }

        $this->connected = true;

        if (!$this->getCapabilitiesFromServer()) {
            throw new SocketException("Failed to read capabilities from the server");
        }
        if ($this->authenticate($username, $password, $tls, $authz_id, $auth_mechanism)) {
            return true;
        }
        throw new SieveException("Error while trying to connect to ManageSieve");
    }

    /**
     * @return void
     */
    public function close() {
        if ($this->connected) {
            try {
                $this->connected = false;
                \fclose($this->sock);
            } catch (\Exception $e) {}
        }
    }

    /**
     * Make sure the socket is closed when
     * the object is freed
     */
    public function __destruct() {
        if ($this->connected) {
            $this->close();
        }
    }

    /**
     * @return string
     */
    public function getServerAddr(): string
    {
        return $this->addr;
    }

    /**
     * Gets list of extensions that server supports.
     *
     * @return array
     */
    public function getExtensions()
    {
        return $this->capabilities['extensions'] ?? [];
    }

    /**
     * Checks if server has extension
     *
     * @param string $extension
     * @return boolean
     */
    public function hasExtension($extension)
    {
        $extension = trim(strtolower($extension));
        if (is_array($this->capabilities['extensions'])) {
            foreach ($this->capabilities['extensions'] as $ext) {
                if ($ext == $extension) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks if server can store script
     *
     * @param string $name The name of the script.
     * @param string $size The size of the script.
     *
     * @return boolean
     */
    public function hasSpace($name, $size)
    {
        $return_payload = $this->sendCommand("HAVESPACE", ['"'.$name.'"', $size]);
        if ($return_payload["code"] == "OK") {
            return true;
        }
        return false;
    }

    /**
     * Verifies script validity without storing the script on the server
     *
     * @param string $name The name of the script.
     * @param string $size The size of the script.
     *
     * @return boolean
     */
    public function checkScript($content)
    {
        $content = $this->prepareContent($content);
        $return_payload = $this->sendCommand("CHECKSCRIPT", [$content]);
        if ($return_payload["code"] == "OK") {
            return true;
        }
        return false;
    }

    /**
     * Used for re-synchronization or to reset any inactivity auto-logout timer on the server.
     *
     * @param string $name The name of the script.
     * @param string $size The size of the script.
     *
     * @return boolean
     */
    public function noop()
    {
        $return_payload = $this->sendCommand("NOOP");
        if ($return_payload["code"] == "OK") {
            return true;
        }
        return false;
    }
}