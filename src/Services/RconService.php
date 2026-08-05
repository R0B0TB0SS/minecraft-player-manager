<?php

namespace R0B0TB0SS\GamePlayerManager\Services;

class RconService
{
    private $socket;
    private $host;
    private $port;
    private $password;
    private $timeout;
    private $authorized = false;
    private $lastError = '';

    const PACKET_AUTHORIZE = 3;
    const PACKET_COMMAND = 2;
    const SERVERDATA_RESPONSE_VALUE = 0;
    const SERVERDATA_AUTH_RESPONSE = 2;

    public function __construct(string $host, int $port, string $password, int $timeout = 3)
    {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        $this->timeout = $timeout;
    }

    public function connect(): bool
    {
        try {
            $this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);

            if (!$this->socket) {
                $this->lastError = "Connection failed: $errno - $errstr";
                \Illuminate\Support\Facades\Log::error("RCON Connection Error [{$this->host}:{$this->port}]: $errno - $errstr");
                return false;
            }

            stream_set_timeout($this->socket, $this->timeout);

            if (!$this->authorize()) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            $this->lastError = "Connection Exception: " . $e->getMessage();
            \Illuminate\Support\Facades\Log::error("RCON Exception [{$this->host}:{$this->port}]: " . $e->getMessage());
            return false;
        }
    }

    public function disconnect()
    {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    private function authorize(): bool
    {
        try {
            $packet = $this->writePacket(self::PACKET_AUTHORIZE, $this->password);
            $response = $this->readPacket();

            if (empty($response)) {
                $this->lastError = "Auth response empty";
                \Illuminate\Support\Facades\Log::error("RCON Auth Failed [{$this->host}]: Empty response");
                return false;
            }

            // Auth response type is 2, id matches
            if ($response['type'] == self::SERVERDATA_AUTH_RESPONSE && $response['id'] == $packet['id']) {
                $this->authorized = true;
                return true;
            }

            $this->lastError = "Auth failed (Wrong password? Type: {$response['type']}, ID: {$response['id']} vs {$packet['id']})";
            \Illuminate\Support\Facades\Log::error("RCON Auth Failed [{$this->host}]: " . $this->lastError);
            return false;
        } catch (\Exception $e) {
            $this->lastError = "Auth Exception: " . $e->getMessage();
            \Illuminate\Support\Facades\Log::error("RCON Auth Exception: " . $e->getMessage());
            return false;
        }
    }

    public function sendCommand(string $command): ?string
    {
        if (!$this->authorized) {
            return null;
        }

        $this->writePacket(self::PACKET_COMMAND, $command);
        $response = $this->readPacket();
        
        return $response['body'] ?? '';
    }

    private function writePacket(int $type, string $body): array
    {
        $id = rand(1, 10000);
        $packet = pack('VV', $id, $type) . $body . "\x00\x00"; // Body + 2 null bytes
        $size = strlen($packet);
        
        fwrite($this->socket, pack('V', $size) . $packet);

        return ['id' => $id];
    }

    private function readPacket(): array
    {
        $sizeData = fread($this->socket, 4);
        if (strlen($sizeData) < 4) return [];
        
        $size = unpack('V', $sizeData)[1];
        if ($size > 4096 || $size < 0) return []; // Sanity check

        $packetData = fread($this->socket, $size);
        if (strlen($packetData) < $size) return [];

        // Parsing packet: ID (4), Type (4), Body (Sort of)
        // Note: Packet format is ID (int), Type (int), Body (string), Null (1), Null (1)
        // But we already read the whole chunk.
        
        $data = unpack('Vid/Vtype', substr($packetData, 0, 8));
        $body = substr($packetData, 8, -2); // Remove last 2 null bytes

        return [
            'id' => $data['id'],
            'type' => $data['type'],
            'body' => $body
        ];
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }
}
