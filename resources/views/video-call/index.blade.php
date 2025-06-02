@extends('layouts.app')

@section('content')
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Welcome to Video Chat</h1>
            <form action="{{ route('video-call.create') }}" method="POST" class="space-y-4">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Start New Call
                </button>
            </form>
        </div>
    </div>
</div>
@endsection 