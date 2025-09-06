@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h2 class="mb-4 fw-bold">Channel Dashboard</h2>

        <div class="row g-3">
            {{-- Payments --}}
            <x-dashboard-card title="TOTAL COLLECTED PAYMENT" icon="fa-indian-rupee-sign" value="{{ number_format($channelTotalPayments, 2) }}" color="danger" />
            <x-dashboard-card title="MONTHLY COLLECTED PAYMENT" icon="fa-indian-rupee-sign" value="{{ number_format($channelMonthlyPayments, 2) }}" color="warning" />
            <x-dashboard-card title="DAILY COLLECTED PAYMENT" icon="fa-indian-rupee-sign" value="{{ number_format($channelDailyPayments, 2) }}" color="info" />

            {{-- Views --}}
            <x-dashboard-card title="TOTAL VIDEOS VIEWS" icon="fa-eye" value="{{ number_format($channelTotalViews) }}" color="primary" />
            <x-dashboard-card title="MONTHLY VIDEOS VIEWS" icon="fa-eye" value="{{ number_format($channelMonthlyViews) }}" color="success" />
            <x-dashboard-card title="DAILY VIDEOS VIEWS" icon="fa-eye" value="{{ number_format($channelDailyViews) }}" color="primary" />

            {{-- Content --}}
            <x-dashboard-card title="MOVIES" icon="fa-film" value="{{ $channelMovieCount }}" color="danger" />
            <x-dashboard-card title="TOTAL VIDEOS" icon="fa-desktop" value="{{ $channelTotalVideos }}" color="danger" />

            {{-- Channel Info --}}
            <x-dashboard-card title="CHANNEL SUBSCRIBERS" icon="fa-users" value="{{ $channelSubscribersCount }}" color="success" />
            <x-dashboard-card title="CHANNEL NAME" icon="fa-broadcast-tower" value="{{ $channel->channel_name ?? 'N/A' }}" color="info" />
            <x-dashboard-card title="CHANNEL STATUS" icon="fa-circle-check" value="{{ ucfirst($channel->status ?? 'Unknown') }}" color="warning" />
        </div>
    </div>
@endsection
