<?php

namespace App\Services\Agora;

class RtcTokenBuilder
{
    const ROLE_PUBLISHER = 1;
    const ROLE_SUBSCRIBER = 2;

    const PRIVILEGE_JOIN_CHANNEL = 1;
    const PRIVILEGE_PUBLISH_AUDIO_STREAM = 2;
    const PRIVILEGE_PUBLISH_VIDEO_STREAM = 3;
    const PRIVILEGE_PUBLISH_DATA_STREAM = 4;

    public static function buildTokenWithUid($appId, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs)
    {
        $token = new AccessToken2($appId, $appCertificate, $privilegeExpiredTs);
        $serviceRtc = new ServiceRtc($channelName, $uid);

        $serviceRtc->addPrivilege($serviceRtc::PRIVILEGE_JOIN_CHANNEL, $privilegeExpiredTs);
        if ($role == self::ROLE_PUBLISHER) {
            $serviceRtc->addPrivilege($serviceRtc::PRIVILEGE_PUBLISH_AUDIO_STREAM, $privilegeExpiredTs);
            $serviceRtc->addPrivilege($serviceRtc::PRIVILEGE_PUBLISH_VIDEO_STREAM, $privilegeExpiredTs);
            $serviceRtc->addPrivilege($serviceRtc::PRIVILEGE_PUBLISH_DATA_STREAM, $privilegeExpiredTs);
        }
        $token->addService($serviceRtc);

        return $token->build();
    }
}
