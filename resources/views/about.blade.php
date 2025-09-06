@extends('layouts.front')

@section('title', 'About')

@section('content')
{{--About Us--}}
<section class="bg-white text-gray-800 mt-9 py-16 px-4 md:px-6" id="about-us">
    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 items-center gap-10">
        <div data-aos="fade-right">
            <h3 class="text-3xl md:text-4xl font-bold mb-4 text-[#fea500]">About Us</h3>
            <p class="text-base md:text-lg mb-6 leading-relaxed">
                <strong>Multiplex Play</strong> is India’s new platform for <span class="text-[#fea500] font-semibold">Unlimited Entertainment</span> featuring exclusive content and a mix of free and premium videos.
            </p>
            <ul class="space-y-2 md:space-y-3 text-gray-700 text-sm md:text-base">
                <li class="flex items-start gap-2"><span class="text-[#fea500]">✔️</span> Download from Play Store easily.</li>
                <li class="flex items-start gap-2"><span class="text-[#fea500]">✔️</span> New users can sign up and enjoy instantly.</li>
                <li class="flex items-start gap-2"><span class="text-[#fea500]">✔️</span> Tons of free and premium content.</li>
                <li class="flex items-start gap-2"><span class="text-[#fea500]">✔️</span> Lowest cost for top-tier streaming.</li>
                <li class="flex items-start gap-2"><span class="text-[#fea500]">✔️</span> All genres: Thriller, Action, Romance & more.</li>
            </ul>
            <a href="#contact-us" class="mt-6 inline-block bg-[#fea500] text-white px-5 py-2 rounded-full font-semibold hover:bg-orange-500 transition text-sm md:text-base">Contact Us</a>
        </div>
        <div data-aos="fade-left">
            <img src="https://multiplexplay.com/img/logo1.png" alt="Entertainment" class="rounded-xl w-full max-w-xs md:max-w-md mx-auto drop-shadow-md" loading="lazy" />
        </div>
    </div>
</section>
@endsection
