@props(['title', 'price', 'device', 'quality', 'highlight' => false])

<div class="p-6 rounded-lg border @if($highlight ?? '') border-[#FF2D20] bg-gray-800 shadow-lg @else border-gray-700 @endif">
    <h4 class="text-xl font-bold mb-2 text-[#FF2D20]">{{ $title ?? '' }}</h4>
    <p class="mb-2 text-gray-400">{{ $quality ?? '' }} quality, {{ $device ?? '' }} device{{ $device > 1 ? 's' : ''  ?? ''}}</p>
    <p class="text-3xl font-bold mb-4">₹{{ $price ?? '' }}/mo</p>
    <button class="bg-[#FF2D20] px-6 py-2 rounded-full font-semibold hover:bg-red-600">Subscribe</button>
</div>
