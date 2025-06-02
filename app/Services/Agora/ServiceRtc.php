<?php

namespace App\Services\Agora;

class ServiceRtc
{
    const SERVICE_TYPE = 1;
    const PRIVILEGE_JOIN_CHANNEL = 1;
    const PRIVILEGE_PUBLISH_AUDIO_STREAM = 2;
    const PRIVILEGE_PUBLISH_VIDEO_STREAM = 3;
    const PRIVILEGE_PUBLISH_DATA_STREAM = 4;

    public $channelName;
    public $uid;
    public $privileges = [];

    public function __construct($channelName, $uid)
    {
        $this->channelName = $channelName;
        $this->uid = $uid;
    }

    public function addPrivilege($privilege, $expire)
    {
        $this->privileges[$privilege] = $expire;
    }

    public function pack()
    {
        $data = [self::SERVICE_TYPE];

        $channelNameBytes = unpack('C*', $this->channelName);
        $data = array_merge($data, [count($channelNameBytes)], $channelNameBytes);


        $uidBytes = unpack('C*', (string)$this->uid);
        $data = array_merge($data, [count($uidBytes)], $uidBytes);

      
        $data[] = count($this->privileges);
        foreach ($this->privileges as $privilege => $expire) {
            $data = array_merge($data, [$privilege, $expire]);
        }

        return $data;
    }
} 