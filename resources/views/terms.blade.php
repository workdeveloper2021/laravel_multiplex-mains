@extends('layouts.front')

@section('title', 'About')

@section('content')

    <main class="p-6 pt-[12vh] pb-[12vh] bg-gray-100">
    <section id="content-access" class="bg-white p-6 mb-6 rounded-lg shadow-lg border-l-4 border-blue-500">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Content Access</h2>
        <p class="text-gray-700 mb-4">MULTIPLEX PLAY offers Content through various modes such as video on demand and offline download, subject to the discretion of MULTIPLEX PLAY.</p>
        <p class="text-gray-700 mb-4">Availability depends on your geographical location. Some Content or Services may not be available to all viewers. Access to Content may depend on the device, specifications, internet availability, and speed.</p>
        <p class="text-gray-700">MULTIPLEX PLAY may monitor IP addresses to determine your location.</p>
    </section>

    <section id="content-models" class="bg-white p-6 mb-6 rounded-lg shadow-lg border-l-4 border-red-500">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Content Models</h2>
        <ul class="text-gray-700 space-y-4">
            <li><strong class="text-blue-600">Free Access:</strong> May include advertisements/commercials.</li>
            <li><strong class="text-blue-600">Subscription Model:</strong> Requires a fee to access certain Content.</li>
            <li><strong class="text-blue-600">Pay-Per-View Model:</strong> May include or exclude advertisements.</li>
            <li><strong class="text-blue-600">Combination:</strong> A mix of the above models.</li>
        </ul>
    </section>

    <section id="device-requirements" class="bg-white p-6 mb-6 rounded-lg shadow-lg border-l-4 border-green-500">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Compatible System and Device Requirements</h2>
        <h3 class="text-xl font-medium text-gray-800 mb-2">Compatible Devices:</h3>
        <p class="text-gray-700 mb-4">The Services can be accessed via mobile phones, tablets, and other connected devices, approved by MULTIPLEX PLAY, which may change over time.</p>

        <h3 class="text-xl font-medium text-gray-800 mb-2">Platform and Software Requirements:</h3>
        <h4 class="text-lg font-medium text-gray-800 mb-2">Website:</h4>
        <ul class="list-disc pl-6 space-y-2 text-gray-700">
            <li>JavaScript and cookies enabled.</li>
            <li>Latest versions of Safari, Firefox, Google Chrome, and Adobe Flash Player.</li>
            <li>Microsoft Windows XP or MAC OS 10.2 or above.</li>
        </ul>

        <h4 class="text-lg font-medium text-gray-800 mb-2">Applications:</h4>
        <ul class="list-disc pl-6 space-y-2 text-gray-700">
            <li>iOS 8 or above.</li>
            <li>Android v.4.0 or above.</li>
        </ul>
    </section>

    <section id="viewer-discretion" class="bg-white p-6 mb-6 rounded-lg shadow-lg border-l-4 border-yellow-500">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Viewer Discretion and Age Restrictions</h2>
        <h3 class="text-xl font-medium text-gray-800 mb-2">Content Suitability:</h3>
        <p class="text-gray-700 mb-4">Some content may not be suitable for all viewers. Viewer discretion is advised. Some content may not be appropriate for children, and parents/guardians should exercise discretion.</p>
        <h3 class="text-xl font-medium text-gray-800 mb-2">Age Requirement:</h3>
        <p class="text-gray-700">The service is available for individuals aged 18 or older. Minors may access the content with adult supervision, subject to applicable laws.</p>
    </section>

    <section id="electronic-communications" class="bg-white p-6 mb-6 rounded-lg shadow-lg border-l-4 border-purple-500">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Electronic Communications</h2>
        <p class="text-gray-700 mb-4">By using the Services, you consent to MULTIPLEX PLAY communicating with you via email, push notifications, or other electronic means.</p>
        <p class="text-gray-700 mb-4">You may be contacted via SMS or email to verify login details and for other communication purposes.</p>
        <p class="text-gray-700">You consent to receiving communication from MULTIPLEX PLAY through electronic records.</p>
    </section>

    <section id="subscription-plans" class="bg-white p-6 mb-6 rounded-lg shadow-lg border-l-4 border-teal-500">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Subscription Plans and Payment</h2>
        <h3 class="text-xl font-medium text-gray-800 mb-2">Subscription Model:</h3>
        <p class="text-gray-700 mb-4">Certain Content requires registration and payment of a subscription fee. Payment can be made using debit/credit cards, internet banking, e-wallets, or cash on delivery (in some cases).</p>
        <h3 class="text-xl font-medium text-gray-800 mb-2">Available Subscription Plans:</h3>
        <ul class="list-disc pl-6 space-y-2 text-gray-700">
            <li>All Access Premium Subscription.</li>
            <li>Limited Subscription Packs, including MULTIPLEX PLAY VIP.</li>
        </ul>

        <h3 class="text-xl font-medium text-gray-800 mb-2">Free Trial:</h3>
        <p class="text-gray-700 mb-4">Some subscription plans may offer a free trial. The trial period and eligibility are determined by MULTIPLEX PLAY and can be modified or terminated at any time.</p>

        <h3 class="text-xl font-medium text-gray-800 mb-2">Refund on Token Payment:</h3>
        <p class="text-gray-700">A token amount may be charged at the time of registration for the subscription plan and refunded once payment confirmation is received.</p>
    </section>

    <section id="social-feed" class="bg-white p-6 mb-6 rounded-lg shadow-lg border-l-4 border-yellow-400">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Social Feed and Community Interaction</h2>
        <h3 class="text-xl font-medium text-gray-800 mb-2">Social Feed:</h3>
        <p class="text-gray-700 mb-4">The Social Feed allows users to share their views, comments, and participate in contests/competitions. You can invite friends to participate, and your social posts will be visible to them based on privacy settings.</p>

        <h3 class="text-xl font-medium text-gray-800 mb-2">Access to Contacts:</h3>
        <p class="text-gray-700">You will be required to permit MULTIPLEX PLAY to access your contact list to enable social interactions with friends.</p>
    </section>

    <section id="terms-and-conditions" class="bg-white p-6 mb-6 rounded-lg shadow-lg border-l-4 border-teal-600">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Terms and Conditions</h2>
        <h3 class="text-xl font-medium text-gray-800 mb-2">Compliance:</h3>
        <p class="text-gray-700 mb-4">Your use of the Site and Services is subject to the Terms and Conditions, Privacy Policy, and applicable laws in your jurisdiction.</p>

        <h3 class="text-xl font-medium text-gray-800 mb-2">Content Protection:</h3>
        <p class="text-gray-700">All Content on the Site is protected by copyright and other intellectual property laws. Use of Content outside the provided terms requires written permission from MULTIPLEX PLAY.</p>
    </section>
</main>
@endsection
