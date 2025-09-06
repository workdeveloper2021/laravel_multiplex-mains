<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="https://multiplexplay.com/storage/banners/1752765686_logo1.png">

    <title>Login - Multiplex Play</title>

    <!-- Fonts and Icons -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Laravel Vite Assets -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(to bottom, #ff5722 50%, #e0e0e0 50%);
            height: 100%;
            overflow: auto;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
    </style>

</head>
<body class="min-h-screen flex flex-col">

<div class="text-white text-center text-3xl font-bold tracking-wider py-8">
    MULTIPLEX PLAY
</div>

<div class="flex flex-1 justify-center items-center px-4">
    <div class="bg-white p-8 md:p-10 rounded-xl shadow-xl w-full max-w-md">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <h2 class="text-2xl font-bold text-orange-600 text-center mb-6 flex items-center justify-center gap-2">
            <i class="fas fa-user text-orange-500"></i>
        </h2>

        <div class="mt-3 text-center grid grid-cols-2 gap-2">
            <a href="{{ route('auth.google') }}"
            class="btn btn-outline-dark w-full">
                <i class="fab fa-google me-2"></i> Register with Google
            </a>
            <a href="{{ route('register.detail') }}"
            class="btn btn-outline-dark w-full">
                <i class="fas fa-user-plus me-2"></i> Register Manually
            </a>
        </div>
    </div>
</div>

</body>
</html>
