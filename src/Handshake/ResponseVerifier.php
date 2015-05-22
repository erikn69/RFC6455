<?php


namespace Ratchet\RFC6455\Handshake;


use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class ResponseVerifier {
    public function verifyAll(Request $request, Response $response) {
        $passes = 0;

        $passes += (int)$this->verifyStatus($response->getStatusCode());
        $passes += (int)$this->verifyUpgrade($response->getHeader('Upgrade'));
        $passes += (int)$this->verifyConnection($response->getHeader('Connection'));
        $passes += (int)$this->verifySecWebSocketAccept(
            $response->getHeader('Sec-WebSocket-Accept'),
            $request->getHeader('sec-websocket-key')
            );

        return (4 == $passes);
    }

    public function verifyStatus($status) {
        return ($status == 101);
    }

    public function verifyUpgrade(array $upgrade) {
        return (in_array('websocket', array_map('strtolower', $upgrade)));
    }

    public function verifyConnection(array $connection) {
        return (in_array('upgrade', array_map('strtolower', $connection)));
    }

    public function verifySecWebSocketAccept($swa, $key) {
        return (
            1 === count($swa) &&
            1 === count($key) &&
            $swa[0] == $this->sign($key[0]));
    }

    public function sign($key) {
        return base64_encode(sha1($key . Negotiator::GUID, true));
    }
}