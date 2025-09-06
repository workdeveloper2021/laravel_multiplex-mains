@extends('layouts.app')

@section('content')
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #ff6339 0%, #ff8e53 100%);
            padding: 2rem 0;
            margin: -1.5rem -1.5rem 2rem -1.5rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .dashboard-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="80" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="40" r="1" fill="rgba(255,255,255,0.05)"/></svg>');
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #495057;
            margin-bottom: 1.5rem;
            padding-left: 1rem;
            border-left: 4px solid #ff6339;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stats-overview {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .welcome-text {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .dashboard-subtitle {
            opacity: 0.8;
            font-size: 0.9rem;
        }
    </style>

    <div class="container-fluid">
        <div class="dashboard-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="welcome-text mb-2">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Welcome to Admin Dashboard
                        </h1>
                        <p class="dashboard-subtitle mb-0">
                            Manage your multiplex platform with powerful insights and controls
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="text-white">
                            <i class="fas fa-calendar-alt me-2"></i>
                            {{ date('F d, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Section -->
        <div class="stats-overview">
            <h3 class="section-title">
                <i class="fas fa-chart-line me-2"></i>
                Admin Revenue Analytics
            </h3>
            <div class="row">
                <x-dashboard-card title="TOTAL COLLECTED PAYMENT" icon="fa-coins" value="₹ {{ number_format($TotalCollectedPayments, 2) }}" color="danger" />
                <x-dashboard-card title="MONTHLY COLLECTED PAYMENT" icon="fa-calendar-check" value="₹ {{ number_format($TotalMonthlyPayments, 2) }}" color="warning" />
                <x-dashboard-card title="DAILY COLLECTED PAYMENT" icon="fa-clock" value="₹ {{ number_format($dailyCollectedPayment, 2) }}" color="info" />
            </div>
        </div>

        <div class="stats-overview">
            <h3 class="section-title">
                <i class="fas fa-chart-line me-2"></i>
                Channel Revenue Analytics
            </h3>
            <div class="row">
                <x-dashboard-card title="DAILY CHANNEL PAYMENT" icon="fa-tv" value="₹ {{ number_format($ChannelDailyAmt, 2) }}" color="success" />
                <x-dashboard-card title="MONTHLY CHANNEL PAYMENT" icon="fa-calendar-alt" value="₹ {{ number_format($ChannelMonthAmt, 2) }}" color="warning" />
                <x-dashboard-card title="TOTAL CHANNEL PAYMENT" icon="fa-broadcast-tower" value="₹ {{ number_format($ChannelTotalAmt, 2) }}" color="primary" />
            </div>
        </div>

        <!-- Engagement Section -->
        <div class="stats-overview">
            <h3 class="section-title">
                <i class="fas fa-eye me-2"></i>
                Viewer Engagement
            </h3>
            <div class="row">
                <x-dashboard-card title="TOTAL VIDEOS VIEWS" icon="fa-play-circle" value="{{ number_format($totalVideoViews) }}" color="primary" />
                <x-dashboard-card title="MONTHLY VIDEOS VIEWS" icon="fa-chart-bar" value="{{ number_format($monthlyVideoViews) }}" color="success" />
                <x-dashboard-card title="DAILY VIDEOS VIEWS" icon="fa-eye" value="{{ number_format($dailyVideoViews) }}" color="info" />
            </div>
        </div>

        <!-- Content Section -->
        <div class="stats-overview">
            <h3 class="section-title">
                <i class="fas fa-video me-2"></i>
                Content Library
            </h3>
            <div class="row">
                <x-dashboard-card title="MOVIES" icon="fa-film" value="{{ $movieCount }}" color="danger" />
                <x-dashboard-card title="WEB-SERIES" icon="fa-video" value="{{ $webSeriesCount }}" color="info" />
                <x-dashboard-card title="TOTAL EPISODES" icon="fa-play-circle" value="{{ $episodesCount }}" color="success" />
                <x-dashboard-card title="CHANNEL VIDEOS" icon="fa-desktop" value="{{ $channelVideosCount }}" color="warning" />
            </div>
        </div>

        <!-- System Section -->
        <div class="stats-overview">
            <h3 class="section-title">
                <i class="fas fa-cogs me-2"></i>
                System Overview
            </h3>
            <div class="row">
                <x-dashboard-card title="ACTIVE CHANNELS" icon="fa-satellite-dish" value="{{ $channelCount }}" color="warning" />
                <x-dashboard-card title="TOTAL USERS" icon="fa-users" value="{{ $userCount }}" color="danger" />
                <x-dashboard-card title="CONTENT GENRES" icon="fa-tags" value="{{ $genreCount }}" color="info" />
            </div>
        </div>
    </div>
@endsection

