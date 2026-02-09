<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\Crypt;

class MqttPublishService
{
    /**
     * Publish a message to MQTT broker using project credentials
     *
     * @param Project $project
     * @param string $topic
     * @param string $payload
     * @param int $qos
     * @param bool $retained
     * @return bool
     */
    public function publish(Project $project, string $topic, string $payload, int $qos = 0, bool $retained = false): bool
    {
        try {
            // Get MQTT broker configuration
            $brokerHost = config('mqtt.host') ?? request()->getHost();
            $brokerPort = config('mqtt.port') ?? 1883;
            $username = $project->project_key;
            $password = $project->project_secret_plain
                ? Crypt::decryptString($project->project_secret_plain)
                : null;

            if (!$password) {
                \Log::error("MQTT publish missing plaintext secret for project {$project->id}");
                return false;
            }

            // Create socket connection to MQTT broker
            $socket = @fsockopen($brokerHost, $brokerPort, $errno, $errstr, 5);

            if (!$socket) {
                \Log::error("MQTT Connection failed to {$brokerHost}:{$brokerPort}: {$errstr} ({$errno})");
                return false;
            }

            // Build MQTT CONNECT packet with username and password
            $connectPacket = $this->buildConnectPacket($username, $password);

            // Send CONNECT packet
            fwrite($socket, $connectPacket);

            // Read CONNACK response
            $response = fread($socket, 4);
            if (empty($response) || ord($response[0]) >> 4 !== 2) { // CONNACK
                \Log::error("MQTT CONNACK failed");
                fclose($socket);
                return false;
            }

            // Build MQTT PUBLISH packet
            $publishPacket = $this->buildPublishPacket($topic, $payload, $qos, $retained);

            // Send PUBLISH packet
            $sent = fwrite($socket, $publishPacket);
            fclose($socket);

            if ($sent === false) {
                \Log::error("Failed to publish to MQTT topic: {$topic}");
                return false;
            }

            \Log::info("Published to MQTT topic: {$topic}");
            return true;

        } catch (\Exception $e) {
            \Log::error("MQTT publish error: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Build MQTT CONNECT packet with username and password
     *
     * @param string $username
     * @param string $password
     * @return string
     */
    private function buildConnectPacket(string $username, string $password): string
    {
        // Fixed header byte for CONNECT
        $byte1 = 0x10;

        // Protocol name: "MQTT"
        $protocolName = "MQTT";
        $protocolNameLen = strlen($protocolName);
        $protocolNameBytes = chr(0) . chr($protocolNameLen) . $protocolName;

        // Protocol version: 4 (MQTT 3.1.1)
        $protocolVersion = chr(4);

        // Connect flags
        $connectFlags = 0xC0; // User name flag (bit 7) + Password flag (bit 6)
        $connectFlags = chr($connectFlags);

        // Keep alive
        $keepAlive = chr(0) . chr(60);

        // Client identifier
        $clientId = "php-publisher-" . uniqid();
        $clientIdLen = strlen($clientId);
        $clientIdBytes = chr(0) . chr($clientIdLen) . $clientId;

        // Username
        $usernameLen = strlen($username);
        $usernameBytes = chr(0) . chr($usernameLen) . $username;

        // Password
        $passwordLen = strlen($password);
        $passwordBytes = chr(0) . chr($passwordLen) . $password;

        // Variable header + payload
        $variableHeader = $protocolNameBytes . $protocolVersion . $connectFlags . $keepAlive;
        $payload = $clientIdBytes . $usernameBytes . $passwordBytes;
        $remaining = $variableHeader . $payload;

        // Remaining length encoding
        $remainingLen = strlen($remaining);
        $lenBytes = $this->encodeRemainingLength($remainingLen);

        return chr($byte1) . $lenBytes . $remaining;
    }

    /**
     * Build MQTT PUBLISH packet
     *
     * @param string $topic
     * @param string $payload
     * @param int $qos
     * @param bool $retained
     * @return string
     */
    private function buildPublishPacket(string $topic, string $payload, int $qos = 0, bool $retained = false): string
    {
        // Fixed header byte
        $byte1 = 0x30; // PUBLISH
        $byte1 |= ($qos & 0x03) << 1; // QoS
        if ($retained) {
            $byte1 |= 0x01; // Retain flag
        }

        // Topic length (2 bytes, big-endian)
        $topicLen = strlen($topic);
        $topicLenBytes = chr(($topicLen >> 8) & 0xFF) . chr($topicLen & 0xFF);

        // Payload
        $payloadBytes = $payload;

        // Variable length encoding for remaining length
        $remaining = $topicLenBytes . $topic . $payloadBytes;
        $remainingLen = strlen($remaining);

        $lenBytes = $this->encodeRemainingLength($remainingLen);

        return chr($byte1) . $lenBytes . $remaining;
    }

    /**
     * Encode remaining length (MQTT variable length encoding)
     *
     * @param int $length
     * @return string
     */
    private function encodeRemainingLength(int $length): string
    {
        $lenBytes = '';
        do {
            $encodedByte = $length % 128;
            $length = floor($length / 128);
            if ($length > 0) {
                $encodedByte |= 0x80;
            }
            $lenBytes .= chr($encodedByte);
        } while ($length > 0);

        return $lenBytes;
    }
}
