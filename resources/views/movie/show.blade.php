@extends('layouts.app')

@section('content')
    <div class="container mt-5">

        {{--  <h2>{{ $movie->title }}</h2>
        <p><strong>Language:</strong> {{ $movie->language }}</p>
        <p><strong>Genres:</strong>
            @foreach ($movie->genre as $genre)
                <span class="badge bg-secondary">{{ $genre }}</span>
            @endforeach
        </p>  --}}

{{--        @if(auth()->check() && auth()->user()->is_admin)  --}}
        <div class="my-4">
            <video id="hls-player" controls width="100%" height="auto"></video>
        </div>
{{--        @endif--}}

    </div>
@endsection

@section('scripts')
    @if(auth()->check() && auth()->user()->is_admin)
        <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const video = document.getElementById('hls-player');
                const hls = new Hls();

                // Example: assuming movie->hls_url contains the .m3u8 file path
                const hlsUrl = "{{ $movie->hls_url }}";

                if (Hls.isSupported()) {
                    hls.loadSource(hlsUrl);
                    hls.attachMedia(video);
                    hls.on(Hls.Events.MANIFEST_PARSED, function () {
                        video.play();
                    });
                } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                    video.src = hlsUrl;
                    video.addEventListener('loadedmetadata', function () {
                        video.play();
                    });
                }
            });
        </script>
    @endif
@endsection
