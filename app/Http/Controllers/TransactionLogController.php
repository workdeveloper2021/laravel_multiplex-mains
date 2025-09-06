<?php

namespace App\Http\Controllers;

use App\Models\Subscriptions;
use App\Models\User;
use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use MongoDB\BSON\ObjectId;


class TransactionLogController extends Controller
{
    /**
    * Display a listing of the resource.
    */
        public function index(Request $request)
        {
            if ($request->ajax()) {
                $draw = intval($request->input('draw'));
                $start = intval($request->input('start', 0)); // e.g., 0, 10, 20
                $length = intval($request->input('length', 10)); // page size

                // Filter by user role
                $user = Auth::user();
                $query = Subscriptions::query();

                if ($user->role === 'channel') {
                    //     // For channel users, find their channel and show transactions for that channel
                    //     $channel = Channel::where('user_id', $user->id)->first();
                    //     if ($channel) {
                    //         $query->where('channel_id', (string) $channel->_id);
                    //     } else {
                    //         $query->where('channel_id', 'no_channel_found'); // Empty result
                    //     }
                    $user_id = new ObjectId($user->channel_id);
                    // dd($user_id);
                    $query->where('channel_id', $user_id);
                }
                // Total count before pagination with filtering
                $totalCount = $query->count();

                // MongoDB pagination using skip & limit with filtering
                $subscriptions = $query->skip($start)->limit($length)->get();
                $transactions = [];

                foreach ($subscriptions as $subscription) {
                    $paymentInfo = $subscription['payment_info'] ?? "";
                    $normalized = [];

                    if (is_array($paymentInfo) && !empty($paymentInfo)) {
                        $normalized = $paymentInfo;
                    } elseif (is_string($paymentInfo) && !empty($paymentInfo)) {
                        $normalized[] = [
                            'transaction_id' => $paymentInfo,
                            'amount' => $subscription['paid_amount'] ?? $subscription['price_amount'] ?? 0,
                            'currency' => $subscription['currency'] ?? 'INR',
                            'status' => $subscription['status'] == 1 ? 'paid' : 'failed',
                        ];
                    } else {
                        // If payment_info is empty, create a default transaction from subscription data
                        $normalized[] = [
                            'transaction_id' => $subscription['receipt'] ?? 'N/A',
                            'amount' => $subscription['paid_amount'] ?? $subscription['price_amount'] ?? 0,
                            'currency' => $subscription['currency'] ?? 'INR',
                            'status' => $subscription['status'] == 1 ? 'paid' : 'failed',
                        ];
                    }

                    foreach ($normalized as $info) {
                        if (is_string($info)) {
                            $info = json_decode($info, true);
                        }

                        if (!is_array($info)) continue;

                        $subUser = User::find($subscription['user_id']);
                        $userName = $subUser->name ?? 'Unknown';
                        $userEmailPhone = $subUser->email ?? $subUser->phone ?? 'Unknown';

                        $transactions[] = [
                            'id' => (string) ($subscription['_id'] ?? ''),
                            'user' => $userName,
                            'email' => $userEmailPhone,
                            'transaction_type' => $subscription['payment_method'] ?? 'Unknown',
                            'amount' => $info['amount'] ?? 0,
                            'status' => $info['status'] ?? 'unknown',
                            'created_at' => date(
                                'Y-m-d H:i:s',
                                is_numeric($subscription['created_at'])
                                    ? $subscription['created_at'] / 1000
                                    : strtotime($subscription['created_at'])
                            )
                        ];
                    }
                }

                return response()->json([
                    'draw' => $draw,
                    'recordsTotal' => $totalCount,
                    'recordsFiltered' => $totalCount, // You can change this if filtering is added
                    'data' => $transactions
                ]);
            }

            return view('transaction_log.index');
        }






    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
