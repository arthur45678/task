<?php

namespace App\Services\Agora;

class AccessToken2
{
    const VERSION = "007";
    const VERSION_LENGTH = 3;

    public $appId;
    public $appCertificate;
    public $expire;
    public $services = [];

    public function __construct($appId, $appCertificate, $expire)
    {
        $this->appId = $appId;
        $this->appCertificate = $appCertificate;
        $this->expire = $expire;
    }

    public function addService($service)
    {
        $this->services[] = $service;
    }

    public function build()
    {
        $signing = $this->getSign();
        $data = array_merge([$this->appId, $this->expire], $signing);

        foreach ($this->services as $service) {
            $data = array_merge($data, $service->pack());
        }

        $packedData = pack("C*", ...$data);
        $signature = hash_hmac('sha256', $packedData, pack("C*", ...$signing), true);

        return self::VERSION . base64_encode($signature . $packedData);
    }

    private function getSign()
    {
        $signing = [];
        for ($i = 0; $i < strlen($this->appCertificate); $i += 2) {
            $signing[] = hexdec(substr($this->appCertificate, $i, 2));
        }
        return $signing;
    }
} 