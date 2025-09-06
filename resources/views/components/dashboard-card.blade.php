@php
    $orangeCards = [
        'TOTAL COLLECTED PAYMENT',
        'MONTHLY COLLECTED PAYMENT',
        'MONTHLY CHANNEL COLLECTED PAYMENT',
        'TOTAL CHANNEL COLLECTED PAYMENT',
        'DAILY COLLECTED PAYMENT',
        'TOTAL VIDEOS VIEWS',
        'MONTHLY VIDEOS VIEWS',
        'DAILY VIDEOS VIEWS',
        'MOVIES'
    ];

    $cardColors = [
        'WEB-SERIES' => 'info',
        'TOTAL EPISODES' => 'success',
        'CHANNELS' => 'warning',
        'CHANNEL VIDEOS' => 'danger',
        'GENRES' => 'info',
        'USERS' => 'danger',
        'CHANNEL CREATOR' => 'danger'
    ];
@endphp

<style>
    .creative-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
        overflow: hidden;
        position: relative;
    }

    .creative-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }

    .creative-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #ff6339, #ff8e53, #ffb347, #ff6339, #ff8e53);
        background-size: 300% 300%;
        animation: gradient 3s ease infinite;
    }

    @keyframes gradient {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    .icon-container {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .icon-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.3) 100%);
        border-radius: 50%;
    }

    .bg-custom-orange {
        background: linear-gradient(135deg, #ff6339 0%, #ff8e53 100%);
    }

    .bg-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .bg-success {
        background: linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%);
    }

    .bg-info {
        background: linear-gradient(135deg, #45b7d1 0%, #96c93d 100%);
    }

    .bg-warning {
        background: linear-gradient(135deg, #feca57 0%, #ff9ff3 100%);
    }

    .bg-danger {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    }

    .card-value {
        font-size: 1.8rem;
        font-weight: 800;
        color: #2c3e50;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    }

    .card-title-custom {
        font-size: 0.85rem;
        font-weight: 600;
        color: #7f8c8d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .pulse-icon {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
</style>

<div class="col-md-6 col-lg-4 col-xl-3 mb-4">
    <div class="creative-card">
        <div class="card-body p-4 d-flex align-items-center">
            <div class="icon-container me-3 bg-{{ $color ?? (in_array($title ?? '', $orangeCards ?? '') ? 'custom-orange' : ($cardColors[$title ?? ''] ?? 'primary')) }}">
                <i class="fas {{ $icon }} fa-lg text-white pulse-icon"></i>
            </div>
            <div class="flex-grow-1">
                <p class="card-title-custom mb-1">{{ $title }}</p>
                <h4 class="card-value mb-0">{{ $value }}</h4>
            </div>
        </div>
    </div>
</div>
