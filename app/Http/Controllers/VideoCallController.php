<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VideoCallController extends Controller
{
    public function index()
    {
        return view('video-call.index');
    }

    public function room($roomId)
    {
        return view('video-call.room', [
            'roomId' => $roomId,
            'agoraAppId' => config('agora.app_id')
        ]);
    }

    public function createRoom()
    {
        $roomId = uniqid('room_');
        return redirect()->route('video-call.room', $roomId);
    }
}
