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
            <i class="fas fa-user text-orange-500"></i> Sign Up
        </h2>

        <form action="{{ route('register') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <!-- Channel Name -->
            <div>
                <input type="text" name="channel_name" placeholder="Channel Name" required
                       class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-orange-500 focus:outline-none" value="{{ old('channel_name') }}" />
            </div>

            <!-- Email -->
            <div>
                <input type="email" name="email" placeholder="Email" required
                       class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-orange-500 focus:outline-none" value="{{ old('email') }}" />
            </div>

            <!-- Password -->
            <div>
                <input type="password" name="password" placeholder="Password" required
                       class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-orange-500 focus:outline-none" />
            </div>

            <!-- Name -->
            <div>
                <input type="text" name="name" placeholder="Your Full Name" required
                       class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-orange-500 focus:outline-none" value="{{ old('name') }}" />
            </div>

            <!-- Mobile -->
            <div>
                <input type="text" name="mobile" placeholder="Mobile Number" required
                       class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-orange-500 focus:outline-none" value="{{ old('mobile') }}" />
            </div>

            {{--  <!-- Address -->
            <div>
                <input type="text" name="address" placeholder="Address" required
                       class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-orange-500 focus:outline-none" />
            </div>  --}}

            <!-- Organization Name -->
            <div>
                <input type="text" name="organization_name" placeholder="Organization Name" required
                       class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-orange-500 focus:outline-none" value="{{ old('organization_name') }}" />
            </div>

            <!-- Organization Address -->
            <div>
                <input type="text" name="organization_address" placeholder="Organization Address" required
                       class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-orange-500 focus:outline-none" value="{{ old('organization_address') }}" />
            </div>

            <!-- Document Upload -->
            <div>
                <label for="document" class="block text-sm font-medium text-gray-700">Upload ID Proof</label>
                <input type="file" name="document" required
                       class="w-full px-4 py-2 border rounded-md bg-gray-50 text-gray-700
                      file:mr-4 file:py-2 file:px-4 file:rounded file:border-0
                      file:text-sm file:bg-orange-100 file:text-orange-700 hover:file:bg-orange-200" />
            </div>

            <!-- Login Redirect -->
            <div class="text-center">
                <a href="{{ route('login') }}" class="text-orange-600 hover:underline text-sm font-medium">
                    Already have an account?
                </a>
            </div>

            <!-- Submit -->
            <button type="submit"
                    class="w-full py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-md text-lg font-semibold transition">
                SIGN UP
            </button>
        </form>

    </div>
</div>

</body>
</html>
