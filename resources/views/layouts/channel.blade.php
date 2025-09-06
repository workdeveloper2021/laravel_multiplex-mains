<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts and Icons -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>

        #sidebar {
            transition: all 0.3s ease;
            height: calc(100vh - 56px);
            width: 290px;
            overflow-y: auto;
            position: relative;
            left: 0;
            background-color: #fff;
            border-right: 1px solid #ddd;
            z-index: 1000;
            /* Webkit (Chrome, Safari, Edge) scrollbar styling */
            &::-webkit-scrollbar {
                width: 8px;
            }

            &::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 4px;
            }

            &::-webkit-scrollbar-thumb {
                background: #ff6339;
                border-radius: 4px;
            }

            &::-webkit-scrollbar-thumb:hover {
                background: #ff4f1f;
            }

            /* Firefox scrollbar styling */
            scrollbar-width: thin;
            scrollbar-color: #ff6339 #f1f1f1;


        }
        #sidebar.collapsed {
            width: 0;
            overflow: hidden;
        }

        #main-content {
            flex-grow: 1;
            overflow-y: auto;
            height: 100%;
            transition: margin-left 0.3s ease;
            width: calc(100% - 260px);
            padding: 1rem;
        }

        #main-content.expanded {
            margin-left: 0 !important;
            width: 100%;

        }

        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.25rem;
            color: #333;
        }

        #sidebar .nav-link:hover {
            background-color: #ff6339;
            color: white !important;
            border-radius: 4px;
        }

        #sidebar .nav-link i {
            transition: color 0.3s ease;
        }

        #sidebar .nav-link:hover i {
            color: white;
        }

        @media (max-width: 768px) {
            #sidebar {
                position: absolute;
                margin-left: 0;
                z-index: 1000;
                height: calc(100vh - 56px);
            }
        }

    </style>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<div id="app">
    @php
        $hideNavbarRoutes = ['login', 'register', 'password.request', 'password.reset', 'user-login', 'verify'];
    @endphp

    @if (!in_array(Route::currentRouteName(), $hideNavbarRoutes))
        <nav class="navbar navbar-expand-md navbar-light shadow-sm fixed-top" style="background-color: #ff6339;">
            <div class="container">
                {{-- Sidebar Toggle --}}
                <a class="navbar-brand text-white fw-bold" href="{{ url('/') }}" style="font-size: 20px;">
                    {{ config('app.name', 'Multiplex Play') }}
                </a>
                @if(Auth::check())
                    <button id="sidebarToggle" class="sidebar-toggle me-3">
                        <i class="fas fa-bars"></i>
                    </button>
                @endif

                <ul class="navbar-nav ms-auto">
                    @guest
                        @if (Route::has('login'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                        @endif
                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                            </li>
                        @endif
                    @endguest
                </ul>
            </div>
        </nav>
    @endif

    {{-- Main Area with Sidebar --}}
    @if (Auth::check() && !in_array(Route::currentRouteName(), $hideNavbarRoutes))
        <div class="d-flex" style="margin-top: 56px; height: calc(100vh - 56px); overflow: hidden;">
            {{-- Sidebar --}}
            <div id="sidebar">
                @include('layouts.sidebar')
            </div>

            {{-- Page Content --}}
            <main id="main-content" class="flex-grow-1 p-4">
                @yield('content')
            </main>
        </div>
    @else
        {{-- Auth Not Logged In --}}
        <main class="py-4">
            @yield('content')
        </main>
    @endif
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

{{-- Sidebar Toggle Script --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        const mainContent = document.getElementById('main-content');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function () {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            });
        }
    });
</script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#1e40af',
                },
            },
        },
    };
</script>
@stack('scripts')
</body>
</html>
