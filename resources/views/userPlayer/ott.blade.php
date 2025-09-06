@extends('layouts.app')

@section('content')
    <div class="bg-gray-900 text-white min-h-screen p-4">
        <h1 class="text-3xl font-bold mb-6">Secure Streaming</h1>

        <div class="aspect-video w-full mb-8">
            <video id="hls-video" class="w-full h-full rounded-lg shadow-lg" controls></video>
        </div>

        <h2 class="text-2xl font-semibold mb-4">Episodes</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            @foreach ($videos as $video)
                <div class="bg-gray-800 rounded-lg overflow-hidden shadow-md hover:shadow-xl transition">
                    <div class="aspect-video">
                        <video class="w-full h-full object-cover" muted>
                            <source src="{{ route('stream.secure', ['id' => $video->id, 'token' => $video->secure_token]) }}" type="application/x-mpegURL">
                        </video>
                    </div>
                    <div class="p-3">
                        <h3 class="text-lg font-semibold">{{ $video->title }}</h3>
                        <button
                            class="mt-2 px-3 py-1 bg-blue-600 rounded hover:bg-blue-700"
                            onclick="loadStream('{{ route('stream.secure', ['id' => $video->id, 'token' => $video->secure_token]) }}')"
                        >
                            Play Now
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <script>
        function loadStream(url) {
            const video = document.getElementById('hls-video');
            if (Hls.isSupported()) {
                const hls = new Hls();
                hls.loadSource(url);
                hls.attachMedia(video);
            } else if (video.canPlayType('application/vnd.appjle.mpegurl')) {
                video.src = url;
            }
            video.play();
        }
    </script>
@endsection
