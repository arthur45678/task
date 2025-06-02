<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Agora\RtcTokenBuilder;

class AgoraTokenController extends Controller
{
    public function generateToken(Request $request, $channelName)
    {
        $appID = config('agora.app_id');
        $appCertificate = config('agora.app_certificate');
        $uid = 0; 
        $role = RtcTokenBuilder::ROLE_PUBLISHER;
        $expireTimeInSeconds = 3600;
        $currentTimestamp = time();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

        $token = RtcTokenBuilder::buildTokenWithUid(
            $appID,
            $appCertificate,
            $channelName,
            $uid,
            $role,
            $privilegeExpiredTs
        );

        return response()->json(['token' => $token]);
    }
}