@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg">
                <div class="card-header bg-primary bg-gradient d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title text-white mb-0">Room: {{ $roomId }}</h5>
                    <button onclick="copyRoomLink()" class="btn btn-light btn-sm">
                        Copy Room Link
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="position-relative video-container" style="min-height: 80vh;">
                        <!-- Remote Video -->
                        <div id="remote-video" class="w-100 h-100 bg-dark position-absolute top-0 start-0" style="min-height: 80vh;"></div>

                        <!-- Local Video -->
                        <div id="local-video" class="position-absolute shadow-lg rounded-3 overflow-hidden"
                             style="width: 280px; height: 210px; right: 20px; bottom: 90px; z-index: 1;"></div>

                        <!-- Controls Bar -->
                        <div class="position-absolute bottom-0 start-0 w-100 bg-dark bg-opacity-75 p-3" style="z-index: 2;">
                            <div class="d-flex justify-content-center gap-4">
                                <button id="mic-btn" class="btn btn-light rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                    <i class="fas fa-microphone fa-lg"></i>
                                </button>
                                <button id="video-btn" class="btn btn-light rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                    <i class="fas fa-video fa-lg"></i>
                                </button>
                                <button id="leave-btn" class="btn btn-danger rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                    <i class="fas fa-phone-slash fa-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<style>
    .video-container {
        background: #1a1a1a;
        border-radius: 0 0 0.5rem 0.5rem;
    }

    #remote-video {
        background-color: #2a2a2a;
    }

    #local-video {
        background-color: #2a2a2a;
        transition: all 0.3s ease;
    }

    .btn {
        transition: all 0.2s ease;
    }

    .btn:hover {
        transform: scale(1.05);
    }

    .btn:active {
        transform: scale(0.95);
    }

    .btn.disabled {
        background-color: #dc3545 !important;
        color: white !important;
    }

    /* Custom animations */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .card {
        animation: fadeIn 0.5s ease-out;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', async function() {
    const appId = '{{ $agoraAppId }}';
    const channelName = '{{ $roomId }}';
    let agoraClient = null;
    let localAudioTrack = null;
    let localVideoTrack = null;
    let remoteUser = null;

    // Make copyRoomLink globally available
    window.copyRoomLink = function() {
        const url = window.location.href;
        navigator.clipboard.writeText(url).then(() => {
            // Show Bootstrap toast instead of alert
            const toast = document.createElement('div');
            toast.className = 'position-fixed bottom-0 end-0 p-3';
            toast.style.zIndex = '9999';
            toast.innerHTML = `
                <div class="toast show bg-success text-white" role="alert">
                    <div class="toast-body">
                        <i class="fas fa-check-circle me-2"></i>Room link copied to clipboard!
                    </div>
                </div>
            `;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        });
    };

    async function getAgoraToken() {
        try {
            const response = await fetch(`/agora/token/${channelName}`);
            if (!response.ok) {
                throw new Error('Failed to get token');
            }
            const data = await response.json();
            if (data.error) {
                throw new Error(data.error);
            }
            return data.token;
        } catch (error) {
            console.error('Error getting token:', error);
            return null;
        }
    }

    async function initializeAgora() {
        if (typeof AgoraRTC === 'undefined') {
            console.error('Agora SDK not loaded. Retrying in 1 second...');
            setTimeout(initializeAgora, 1000);
            return;
        }

        try {
            const token = await getAgoraToken();
            if (!token) {
                throw new Error('Failed to get Agora token');
            }

            agoraClient = AgoraRTC.createClient({ mode: 'rtc', codec: 'vp8' });

            agoraClient.on('user-published', async (user, mediaType) => {
                await agoraClient.subscribe(user, mediaType);
                remoteUser = user;

                if (mediaType === 'video') {
                    user.videoTrack.play('remote-video');
                }
                if (mediaType === 'audio') {
                    user.audioTrack.play();
                }
            });

            agoraClient.on('user-unpublished', (user, mediaType) => {
                if (mediaType === 'video') {
                    document.getElementById('remote-video').innerHTML = '';
                }
            });

            await agoraClient.join(appId, channelName, token, null);

            localAudioTrack = await AgoraRTC.createMicrophoneAudioTrack();
            localVideoTrack = await AgoraRTC.createCameraVideoTrack();

            await agoraClient.publish([localAudioTrack, localVideoTrack]);
            localVideoTrack.play('local-video');
        } catch (error) {
            console.error('Error initializing Agora:', error);

            const toast = document.createElement('div');
            toast.className = 'position-fixed bottom-0 end-0 p-3';
            toast.style.zIndex = '9999';
            toast.innerHTML = `
                <div class="toast show bg-danger text-white" role="alert">
                    <div class="toast-body">
                        <i class="fas fa-exclamation-circle me-2"></i>${error.message}
                    </div>
                </div>
            `;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 5000);
        }
    }

    document.getElementById('mic-btn').onclick = async function() {
        if (localAudioTrack) {
            if (localAudioTrack.enabled) {
                localAudioTrack.setEnabled(false);
                this.classList.add('disabled');
                this.querySelector('i').classList.remove('fa-microphone');
                this.querySelector('i').classList.add('fa-microphone-slash');
            } else {
                localAudioTrack.setEnabled(true);
                this.classList.remove('disabled');
                this.querySelector('i').classList.remove('fa-microphone-slash');
                this.querySelector('i').classList.add('fa-microphone');
            }
        }
    };

    document.getElementById('video-btn').onclick = async function() {
        if (localVideoTrack) {
            if (localVideoTrack.enabled) {
                localVideoTrack.setEnabled(false);
                this.classList.add('disabled');
                this.querySelector('i').classList.remove('fa-video');
                this.querySelector('i').classList.add('fa-video-slash');
            } else {
                localVideoTrack.setEnabled(true);
                this.classList.remove('disabled');
                this.querySelector('i').classList.remove('fa-video-slash');
                this.querySelector('i').classList.add('fa-video');
            }
        }
    };

    document.getElementById('leave-btn').onclick = async function() {
        if (localAudioTrack) {
            localAudioTrack.close();
        }
        if (localVideoTrack) {
            localVideoTrack.close();
        }
        await agoraClient.leave();
        window.location.href = '/';
    };

    await initializeAgora();
});
</script>
@endsection
