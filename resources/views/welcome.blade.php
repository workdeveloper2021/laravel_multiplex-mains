@extends('layouts.front')
@section('content')
<style>
    .hero-swiper {
        height: auto;
        width: 100%;
        max-height: 70vh;
    }

    .hero-slide {
        position: relative;
        height: auto;
        width: 100%;
        display: block;
    }

    .hero-slide img {
        width: 100%;
        height: auto;
        display: block;
        max-width: 100%;
        max-height: 70vh;
        object-fit: fill;
    }

    @media (min-width: 1024px) {
        .hero-swiper {
            max-height: 80vh;
        }

        .hero-slide img {
            max-height: 80vh;
        }
    }

    .hero-slide::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1;
    }

    .movie-card {
        position: relative;
        overflow: hidden;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        aspect-ratio: 16/9;
    }

    .movie-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .movie-card img {
        width: 100%;
        height: 100%;
        object-fit: fill;
        transition: transform 0.3s ease;
    }

    @media (min-width: 768px) {
        .movie-card img {
            object-fit: fill;
        }
    }

    .movie-card:hover img {
        transform: scale(1.05);
    }

    .swiper-button-prev,
    .swiper-button-next {
        background: rgba(254, 165, 0, 0.9);
        width: 44px;
        height: 44px;
        border-radius: 50%;
        color: white;
        font-size: 18px;
        font-weight: bold;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
    }

    .swiper-button-prev:hover,
    .swiper-button-next:hover {
        background: #fea500;
        transform: scale(1.1);
    }

    .swiper-pagination-bullet {
        background: rgba(255, 255, 255, 0.7);
        opacity: 1;
        width: 12px;
        height: 12px;
    }

    .swiper-pagination-bullet-active {
        background: #fea500;
        transform: scale(1.2);
    }

    .feature-card {
        background: linear-gradient(135deg, #fea500 0%, #ff8c00 100%);
        border-radius: 12px;
        padding: 24px;
        color: white;
        transition: all 0.3s ease;
    }

    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(254, 165, 0, 0.3);
    }


    .contact-card {
        background: white;
        border-radius: 16px;
        padding: 32px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    .hero-content {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hero-section {
        padding-top: 50px;
    }

    @media (max-width: 768px) {
        .hero-section {
            padding-top: 50px;
        }
    }

    /* Mobile Responsiveness Fixes */
    * {
        box-sizing: border-box;
    }

    body {
        overflow-x: hidden;
    }

    .container {
        max-width: 100%;
        padding-left: 1rem;
        padding-right: 1rem;
    }

    /* Mobile text scaling */
    @media (max-width: 480px) {
        .hero-content h1 {
            font-size: 2rem !important;
            line-height: 1.2;
        }

        .hero-content p {
            font-size: 1rem !important;
            line-height: 1.4;
        }

        .hero-content .btn {
            font-size: 0.9rem !important;
            padding: 0.75rem 1.5rem !important;
        }
    }

    /* Swiper mobile fixes */
    .swiper-button-prev,
    .swiper-button-next {
        display: none !important;
    }

    @media (min-width: 768px) {

        .swiper-button-prev,
        .swiper-button-next {
            display: flex !important;
        }
    }
</style>

<!-- Hero Section -->
<section class="hero-section relative overflow-hidden">
    <div class="swiper hero-swiper">
        <div class="swiper-wrapper">

            @if (isset($banners) && $banners->count() > 0)
            @foreach ($banners as $banner)
            <div class="swiper-slide hero-slide">
                <img src="{{ $banner->image_link }}" alt="{{ $banner->title ?? 'MultiplexPlay Banner' }}"
                    loading="lazy">
            </div>
            @endforeach
            @else
            {{-- <div class="swiper-slide hero-slide">
                <img src="https://images.unsplash.com/photo-1489599749187-e0b5b4b43c4b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80"
                    alt="MultiplexPlay Welcome" loading="lazy">
                <div class="hero-content">
                    <div class="text-center text-white max-w-4xl mx-auto px-4">
                        <h1 class="text-4xl md:text-6xl font-bold mb-6 drop-shadow-lg">
                            Welcome to MultiplexPlay
                        </h1>
                        <p class="text-lg md:text-2xl mb-8 drop-shadow-lg opacity-90">
                            Indias Premier Streaming Platform </p>

                        <a href="{{ route('contact') }}"
                            class="inline-flex items-center bg-brand text-white px-8 py-4 rounded-full font-semibold hover:bg-orange-500 transition duration-300 shadow-lg text-lg">
                            Get Started
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div> --}}
            @endif
        </div>
        <div class="swiper-pagination"></div>
    </div>
</section>

<!-- Latest Movies -->
<section class="py-0 px-4 md:px-8 bg-white">

    <div class="max-w-7xl mx-auto">
        {{--  <div class="text-center mb-10">
            <h2 class="text-3xl md:text-5xl font-bold text-gray-800 mb-4">Latest Movies</h2>
        </div>  --}}

        @foreach ($dynamicData as $data)
<div class="text-center mb-1 mt-6">
    <h2 class="text-2xl md:text-2xl font-bold text-gray-800 text-left">{{ $data['name'] }}</h2>
</div>

<div class="swiper latest-movies-swiper mt-1">
    @if (isset($data['videos']) && $data['videos'] > 0)
    <div class="swiper-wrapper">
        @foreach ($data['videos'] as $movie)
        <div class="swiper-slide">
            <div class="movie-card">
                <img src="{{ $movie['poster_url'] ?? 'https://multiplexplay.com/img/logo1.png' }}"
                    alt="{{ $movie['title'] ?? 'Movie' }}" class="w-full h-full object-cover" loading="lazy" />
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="swiper-wrapper">
        @for ($i = 1; $i <= 6; $i++)
        <div class="swiper-slide">
            <div class="movie-card relative">
                <img src="https://via.placeholder.com/300x400?text=Movie+{{ $i }}" alt="Movie {{ $i }}"
                    class="w-full h-full object-cover" loading="lazy" />
                <div class="absolute bottom-0 left-0 right-0 bg-black  p-4">
                    <h3 class="text-white font-semibold text-lg mb-1">Movie Title {{ $i }}</h3>
                    <p class="text-gray-300 text-sm">Now Playing</p>
                </div>
            </div>
        </div>
        @endfor
    </div>
    @endif

    <!-- Navigation Arrows -->
    <div class="swiper-button-prev movies-prev"></div>
    <div class="swiper-button-next movies-next"></div>
</div>
@endforeach


<!-- Latest Web Series -->
{{--  <section class="py-5 px-4 md:px-8 bg-white">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-5xl font-bold text-gray-800 mb-4">Latest Web Series</h2>
        </div>

        <div class="swiper latest-webseries-swiper">
            <div class="swiper-wrapper">
                @if (isset($latestEpisodes) && $latestEpisodes->count() > 0)
                @foreach ($latestEpisodes as $episode)
                <div class="swiper-slide">
                    <div class="movie-card bg-gray-100">
                        <img src="{{ $episode->image_url ?? 'https://multiplexplay.com/storage/banners/1752765686_logo1.png' }}"
                            alt="{{ $episode->title ?? 'Web Series' }}" class="w-full h-full object-fill"
                            loading="lazy" />
                    </div>
                </div>
                @endforeach
                @endif
            </div>
            <div class="swiper-button-prev webseries-prev"></div>
            <div class="swiper-button-next webseries-next"></div>
        </div>
    </div>
</section>  --}}
{{--  @endforeach  --}}



<!-- About Us -->
<section class="py-20 px-4 md:px-8 bg-white" id="about-us">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
            <h3 class="text-3xl md:text-5xl font-bold mb-4 text-gray-800">About <span
                    class="text-brand">MultiplexPlay</span></h3>
            <p class="text-lg md:text-xl text-gray-600 max-w-3xl mx-auto">
                India's premier streaming platform for unlimited entertainment
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 items-center gap-16">
            <div class="order-2 lg:order-1" data-aos="fade-right">
                <p class="text-lg md:text-xl mb-8 leading-relaxed text-gray-700">
                    <strong class="text-brand">MultiplexPlay</strong> is India's new platform for
                    <span class="text-brand font-semibold">Unlimited Entertainment</span> featuring exclusive content
                    and a mix of free and premium videos.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="feature-card">
                        <div class="flex items-center mb-4">
                            <span class="text-3xl mr-4">📱</span>
                            <h4 class="font-bold text-lg">Easy Access</h4>
                        </div>
                        <p class="text-white/90">Download from Play Store and enjoy instantly</p>
                    </div>

                    <div class="feature-card">
                        <div class="flex items-center mb-4">
                            <span class="text-3xl mr-4">🎬</span>
                            <h4 class="font-bold text-lg">Rich Content</h4>
                        </div>
                        <p class="text-white/90">Tons of free and premium content for everyone</p>
                    </div>

                    <div class="feature-card">
                        <div class="flex items-center mb-4">
                            <span class="text-3xl mr-4">💰</span>
                            <h4 class="font-bold text-lg">Affordable</h4>
                        </div>
                        <p class="text-white/90">Lowest cost for top-tier streaming experience</p>
                    </div>

                    <div class="feature-card">
                        <div class="flex items-center mb-4">
                            <span class="text-3xl mr-4">🎭</span>
                            <h4 class="font-bold text-lg">All Genres</h4>
                        </div>
                        <p class="text-white/90">Thriller, Action, Romance & more genres</p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('contact') }}"
                        class="inline-flex items-center justify-center bg-brand text-white px-8 py-4 rounded-full font-semibold hover:bg-orange-500 transition duration-300 shadow-lg">
                        Get Started
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                    <a href="{{ route('about') }}"
                        class="inline-flex items-center justify-center border-2 border-brand text-brand px-8 py-4 rounded-full font-semibold hover:bg-brand hover:text-white transition duration-300">
                        Learn More
                    </a>
                </div>


                <div class="flex flex-col sm:flex-row gap-4 mt-8">
                    <a href="https://play.google.com/store/apps/details?id=app.multiplexplay&hl=en"
                        target="_blank"
                        class="inline-flex items-center justify-center bg-black text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-800 transition duration-300 shadow-lg">
                        <svg class="w-6 h-6 mr-3" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.61 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.53,12.92 20.18,13.18L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z"/>
                        </svg>
                        Download for Android
                    </a>
                    <a href="https://apps.apple.com/app/multiplexplay/id123456789"
                        target="_blank"
                        class="inline-flex items-center justify-center bg-black text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-800 transition duration-300 shadow-lg">
                        <svg class="w-6 h-6 mr-3" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18.71,19.5C17.88,20.74 17,21.95 15.66,21.97C14.32,22 13.89,21.18 12.37,21.18C10.84,21.18 10.37,21.95 9.1,22C7.79,22.05 6.8,20.68 5.96,19.47C4.25,17 2.94,12.45 4.7,9.39C5.57,7.87 7.13,6.91 8.82,6.88C10.1,6.86 11.32,7.75 12.11,7.75C12.89,7.75 14.37,6.68 15.92,6.84C16.57,6.87 18.39,7.1 19.56,8.82C19.47,8.88 17.39,10.1 17.41,12.63C17.44,15.65 20.06,16.66 20.09,16.67C20.06,16.74 19.67,18.11 18.71,19.5M13,3.5C13.73,2.67 14.94,2.04 15.94,2C16.07,3.17 15.6,4.35 14.9,5.19C14.21,6.04 13.07,6.7 11.95,6.61C11.8,5.46 12.36,4.26 13,3.5Z"/>
                        </svg>
                        Download for iOS
                    </a>
                </div>
            </div>

            <div class="order-1 lg:order-1" data-aos="fade-right">
                <div class="relative">
                    <img src="https://multiplexplay.com/storage/banners/1752765686_logo1.png" alt="MultiplexPlay"
                        class="rounded-2xl w-full max-w-md mx-auto drop-shadow-2xl" loading="lazy" />
                    <div
                        class="absolute -top-4 -left-4 w-24 h-24 bg-brand rounded-full flex items-center justify-center text-white font-bold text-xl shadow-lg">
                        <div class="text-center">
                            <div class="text-2xl">HD</div>
                            <div class="text-xs">4K</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="contact-section py-20 px-4 md:px-8">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-5xl font-bold text-gray-800 mb-4">Get in Touch</h2>
            <p class="text-lg md:text-xl text-gray-600">
                Ready to start your entertainment journey? Contact us today!
            </p>
        </div>

        <div class="contact-card">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">NKFC PVT LTD</h3>
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-brand mr-4 mt-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <div>
                                <h4 class="font-semibold text-gray-800 mb-2">Office Address</h4>
                                <p class="text-gray-600 leading-relaxed">
                                    203 Reliable Business Centre<br>
                                    Oshiwara opposite Heera Panna Mall<br>
                                    Andheri West<br>
                                    Mumbai Maharashtra 400053
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-brand mr-4 mt-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                </path>
                            </svg>
                            <div>
                                <h4 class="font-semibold text-gray-800 mb-2">Phone Numbers</h4>
                                <p class="text-gray-600">022 4601 8779</p>
                                <p class="text-gray-600">9920339943</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-brand mr-4 mt-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                            <div>
                                <h4 class="font-semibold text-gray-800 mb-2">Email</h4>
                                <p class="text-gray-600">info@multiplexplay.com</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg overflow-hidden shadow-lg">
                    <iframe class="w-full h-80" frameborder="0" style="border:0" allowfullscreen loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d15077.818!2d72.8294069!3d19.1276952!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sin!4v1683838358694!5m2!1sen!2sin">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Additional bottom padding for fixed footer -->
<div class="h-20"></div>

@endsection
