<?php

namespace MandiriGateway;

use DateTime;

Class Config {
    private $client_id;
    private $client_secret;
    private $access_token;

    public function __construct($client_id, $client_secret) {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
    }

    public function getClientId() {
        return $this->client_id;
    }
    public function getClientSecret() {
        return $this->client_secret;
    }
    public function getAccessToken() {
        return $this->access_token;
    }

    public static function GenerateAccessToken($client_id){
        $private_key_path = 'API_Portal.pem';
        $password = '';

        $timestamp = new DateTime();
        $timestamp->setTimeZone(new \DateTimeZone('Asia/Jakarta'));
        $x_timestamp = $timestamp->format('c');

        $data = $client_id . '|' . $x_timestamp;
        $rsa_algorithm = OPENSSL_ALGO_SHA256;

        $fp = fopen($private_key_path, 'r');
        $privatekey_file = fread($fp, 8192);
        fclose($fp);
        $privatekey = openssl_pkey_get_private($privatekey_file, $password);

        openssl_sign($data, $signature, $privatekey, $rsa_algorithm);

    }

    public static function getSignature($http_method, $endpoint, $data_json, $access_token, $client_secret){
        $data_minify = json_encode(json_decode($data_json));

        $timestamp = new DateTime();
        $timestamp->setTimeZone(new \DateTimeZone('Asia/Jakarta'));
        $x_timestamp = $timestamp->format('c');

        $bin_sha256 = hash('sha256', $data_minify, true);
        $hex_encode = bin2hex($bin_sha256);
        $str_lower = strtolower($hex_encode);

        $full_data = $http_method.':'.$endpoint.':'.$access_token.':'.$str_lower.':'.$x_timestamp;
        $bin_sha512 = hash_hmac('sha_512', $full_data, $client_secret, true);
        $base64 = base64_encode($bin_sha512);

        return $base64;
    }
}