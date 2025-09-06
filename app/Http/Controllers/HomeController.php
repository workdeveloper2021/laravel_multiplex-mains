<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\ChannelSubs;
use App\Models\Genre;
use App\Models\HomeBanner;
use App\Models\Movie;
use App\Models\Subscriptions;
use App\Models\User;
use App\Models\WebSeries;
use App\Models\Episodes;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\ObjectID;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */


    public function index()
    {
        $user = auth()->user();

        switch ($user->role) {
            case 'admin':
                $totalAmount = Subscriptions::sum('amount') / 100; // Convert paise to rupees
                $totalPaidAmount = Subscriptions::sum('paid_amount') / 100; // Convert paise to rupees
                $TotalCollectedPayments = $totalAmount + $totalPaidAmount;

                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now()->addMonth()->startOfMonth();

                $from = $start->timestamp;
                $to = $end->timestamp;

                $currentMonthAmount = Subscriptions::where('created_at', '>=', $from)->where('created_at', '<', $to)
                ->sum('amount') / 100; // Convert paise to rupees



                $ChannelMonthAmt = Subscriptions::where('channel_id', '!=', new ObjectId($user->_id))
                ->where('created_at', '>=', $from)
                ->where('created_at', '<', $to)
                ->sum('amount') / 100; // Convert paise to rupees

                $ChannelTotalAmt = Subscriptions::where('channel_id', '!=', new ObjectId($user->_id))->sum('amount') / 100; // Convert paise to rupees

                $loginWebId = new ObjectID(
                    collect(session()->all())->filter(function ($value, $key) {
                        return str_starts_with($key, 'login_web_');
                    })
                ->first());
                //                dd($loginWebId);
                $AdminchannelAmountTotal = Subscriptions::where('admin_channel_id', $loginWebId)->sum('amount');
                //                dd($channelAmountTotal);
                $TotalMonthlyPayments = $currentMonthAmount ; // - $channelAmountTotal
                //                $TotalMonthlyPayments = Subscriptions::all()->sum('amount');
                $channelCount = Channel::count();


                // Content counts
                $movieCount = Movie::count();
                $webSeriesCount = WebSeries::count();
                $episodesCount = Episodes::count();
                $genreCount = Genre::count();
                $userCount = User::count();

                // Daily calculations
                $todayStart = Carbon::today();
                $todayEnd = Carbon::tomorrow();
                $todayTimestampStart = $todayStart->timestamp;
                $todayTimestampEnd = $todayEnd->timestamp;

                $dailyCollectedPayment = Subscriptions::where('created_at', '>=', $todayTimestampStart)
                    ->where('created_at', '<', $todayTimestampEnd)
                    ->sum('amount') / 100; // Convert paise to rupees

                // Daily Channel Payment calculation
                $ChannelDailyAmt = Subscriptions::where('channel_id', '!=', new ObjectId($user->_id))
                    ->where('created_at', '>=', $todayTimestampStart)
                    ->where('created_at', '<', $todayTimestampEnd)
                    ->sum('amount') / 100; // Convert paise to rupees

                // Video views calculations (placeholder for now - you can implement actual view tracking later)
                // TODO: Implement real view tracking system with views table
                $totalVideoViews = ($movieCount * 1000) + ($episodesCount * 500); // Placeholder calculation
                $monthlyVideoViews = intval($totalVideoViews * 0.3); // 30% of total views this month
                $dailyVideoViews = intval($monthlyVideoViews * 0.1); // 10% of monthly views today

                // Channel videos count
                $channelVideosCount = Movie::whereNotNull('channel_id')->count() +
                                    Episodes::whereNotNull('channel_id')->count();

                return view('dashboard', compact(
                    'TotalCollectedPayments',
                    'TotalMonthlyPayments',
                    'dailyCollectedPayment',
                    'channelCount',
                    'movieCount',
                    'webSeriesCount',
                    'episodesCount',
                    'genreCount',
                    'userCount',
                    'ChannelMonthAmt',
                    'ChannelTotalAmt',
                    'ChannelDailyAmt',
                    'totalVideoViews',
                    'monthlyVideoViews',
                    'dailyVideoViews',
                    'channelVideosCount'
                ));
            case 'channel':
                // Get channel data for the logged-in user
                $channel = Channel::where('user_id', $user->id)->first();
                // dd($channel);
                if (!$channel) {
                    // User has channel role but no channel record - logout and ask to register again
                    Auth::logout();
                    Session::flush();
                    return redirect()->route('login')->with('error', 'Channel registration incomplete. Please register again with your channel details.');
                }

                // Channel specific payments
                $channelTotalPayments = Subscriptions::where('channel_id', (string) $channel->_id)->sum('amount') / 100; // Convert paise to rupees

                // Monthly payments for this channel
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now()->addMonth()->startOfMonth();
                $from = $start->timestamp;
                $to = $end->timestamp;

                $channelMonthlyPayments = Subscriptions::where('channel_id', (string) $channel->_id)
                    ->where('created_at', '>=', $from)
                    ->where('created_at', '<', $to)
                    ->sum('amount') / 100; // Convert paise to rupees

                // Daily payments for this channel
                $todayStart = Carbon::today();
                $todayEnd = Carbon::tomorrow();
                $todayTimestampStart = $todayStart->timestamp;
                $todayTimestampEnd = $todayEnd->timestamp;

                $channelDailyPayments = Subscriptions::where('channel_id', (string) $channel->_id)
                    ->where('created_at', '>=', $todayTimestampStart)
                    ->where('created_at', '<', $todayTimestampEnd)
                    ->sum('amount') / 100; // Convert paise to rupees

                // Channel content counts
                $channelMovieCount = Movie::where('channel_id', (string) $channel->_id)->count();
                $channelWebSeriesCount = WebSeries::where('channel_id', (string) $channel->_id)->count();
                $channelEpisodesCount = Episodes::where('channel_id', (string) $channel->_id)->count();
                $channelTotalVideos = $channelMovieCount + $channelEpisodesCount;

                // Channel video views (placeholder calculation)
                $channelTotalViews = ($channelMovieCount * 1000) + ($channelEpisodesCount * 500);
                $channelMonthlyViews = intval($channelTotalViews * 0.3);
                $channelDailyViews = intval($channelMonthlyViews * 0.1);

                // Channel Subscribers count
                $channelSubscribersCount = ChannelSubs::where('channel', (string) $channel->_id)->count();

                return view('channels.dashboard', compact(
                    'channel',
                    'channelTotalPayments',
                    'channelMonthlyPayments',
                    'channelDailyPayments',
                    'channelMovieCount',
                    'channelWebSeriesCount',
                    'channelEpisodesCount',
                    'channelTotalVideos',
                    'channelTotalViews',
                    'channelMonthlyViews',
                    'channelDailyViews',
                    'channelSubscribersCount'
                ));
            case 'user':
                return view('FrontendPlayer.index');
            default:
                return view('welcome');
        }
    //        return view('dashboard');
    }
}

