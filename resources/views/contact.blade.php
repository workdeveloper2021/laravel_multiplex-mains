@extends('layouts.front')

@section('title', 'Contact Us')

@section('content')
    <section class="max-w-6xl mx-auto pt-[13vh] px-4 md:px-8">
        <h2 class="text-3xl md:text-5xl font-bold text-brand mb-5 text-center">Contact Us</h2>

        <div class="bg-gray-50 p-6 md:p-10 rounded-lg shadow-md mb-5">
            <div class="flex flex-col md:flex-row gap-8">
                <!-- Left: Contact Info -->
                <div class="w-full md:w-1/2">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">NKFC PVT LTD</h3>
                    <p class="text-gray-700 leading-relaxed mb-6">
                        203 Reliable Business Centre<br /> Oshiwara opposite Heera Panna Mall<br /> Andheri West<br /> Mumbai Maharashtra 400053
                    </p>

                    <div class="mb-6">
                        <h4 class="text-lg font-medium text-gray-800 mb-1">Call us</h4>
                        <p class="text-gray-700">022 4601 8779</p>
                        <p class="text-gray-700">9920339943</p>
                    </div>

                    <div>
                        <h4 class="text-lg font-medium text-gray-800 mb-1">Email us</h4>
                        <p class="text-gray-700">info@multiplexplay.com</p>
                    </div>
                </div>

                <!-- Right: Google Map -->
                <div class="w-full md:w-1/2 rounded-lg overflow-hidden shadow-md h-64 md:h-auto">
                    <iframe
                        class="w-full h-full"
                        frameborder="0"
                        style="border:0"
                        allowfullscreen
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d15077.818!2d72.8294069!3d19.1276952!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sin!4v1683838358694!5m2!1sen!2sin">
                    </iframe>
                </div>
            </div>

        </div>
    </section>
@endsection
