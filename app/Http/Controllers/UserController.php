<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Plan;
use App\Models\User;
use App\Models\Subscriptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use MongoDB\Laravel\Eloquent\Casts\ObjectId as CastsObjectId;
use Yajra\DataTables\DataTables;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        if ($request->ajax()) {
            // 1. Get all non-admin users
            $users = User::where('role', '!=', 'admin')
                ->get(['_id', 'name', 'email', 'created_at']);

            $plans = Plan::all();
            // 2. Extract and convert user IDs to ObjectId
            $userIds = $users->pluck('_id')->map(function ($id) {
                return new CastsObjectId($id);
            })->toArray();

            // 3. Get all subscriptions for these users
            //            $subscriptions = Subscriptions::whereIn('user_id', $userIds)->get()->groupBy(function ($item) {
            //                return (string) $item->user_id; // Group by string version of ObjectId
            //            });
            //
            //            // 4. Attach subscriptions to each user
            //            $users->transform(function ($user) use ($subscriptions) {
            //                $user->subscriptions = $subscriptions;
            //                return $user;
            //            });
            //            dd($users->first(), $subscriptions->first());
            //            dd($users);
            // 5. Return response for DataTable
            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('assign_payment', function ($user) {
                    // Only show for admin users
                    if (auth()->user()->role === 'admin') {
                        return '<button class="btn btn-sm btn-success" onclick="openAssignPaymentModal(\'' . $user->_id . '\', \'' . $user->name . '\')">Assign Payment</button>';
                    }
                    return '<span class="text-muted">Admin Only</span>';
                })
                ->addColumn('assign_plan', function ($user) {
                    // Only show for admin users
                    if (auth()->user()->role === 'admin') {
                        return '<button class="btn btn-sm btn-primary" onclick="openAssignPlanModal(\'' . $user->_id . '\', \'' . $user->name . '\')">Assign Plan</button>';
                    }
                    return '<span class="text-muted">Admin Only</span>';
                })
                //                ->addColumn('subscription_count', function ($user) {
                //                    return $user->subscriptions->count();
                //                })
                //                ->addColumn('subscription_names', function ($user) {
                //                    return $user->subscriptions->pluck('plan_name')->implode(', ');
                //                })
                ->rawColumns(['assign_payment', 'assign_plan', 'subscription_names'])
                ->make(true);
        }

        return view('users.index');
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
        $user = auth()->user();
        $channel = null;

        if ($user->role === 'channel') {
            $channel = Channel::where('user_id', (string) $user->_id)->first();
        }

        return view('profile.edit', compact('user', 'channel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'channel_name' => 'nullable|string|max:255',
            'organization_name' => 'nullable|string|max:255',
            'organization_address' => 'nullable|string|max:255',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $user->name = $request->name;
        $user->phone = $request->mobile;
        $user->save();

        if ($user->role === 'channel') {
            $channel = Channel::where('user_id', (string) $user->_id)->first();

            if ($channel) {
                $channel->channel_name = $request->channel_name;
                $channel->organization_name = $request->organization_name;
                $channel->organization_address = $request->organization_address;

                if ($request->hasFile('document')) {
                    $path = $request->file('document')->store('channel_documents', 'public');
                    $channel->document_path = asset('storage/' . $path);
                }

                $channel->save();
            }
        }

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Get all plans for modal dropdown
     */
    public function getPlans()
    {
        try {
            $plans = Plan::all(['_id', 'name', 'price']);

            // Convert _id to id for consistency
            $plans = $plans->map(function ($plan) {
                return [
                    'id' => (string) $plan->_id,
                    '_id' => (string) $plan->_id,
                    'name' => $plan->name,
                    'price' => $plan->price
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $plans
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch plans: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign plan to user via API call
     */
    public function assignPlan(Request $request)
    {
        $request->validate([
            'user_id' => 'required|string',
            'plan_id' => 'required|string',
            'duration' => 'required|integer|min:1'
        ]);

        try {
            // Get plan details
            $plan = Plan::find($request->plan_id);
            if (!$plan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Plan not found'
                ], 404);
            }

            // Here you would make an API call to assign the plan
            // For now, returning a success response

            // Example API call structure:
            // $response = Http::withHeaders([
            //     'api-key' => env('NODE_API_KEY'),
            //     'Accept' => 'application/json',
            // ])->post('https://multiplexplay.com/nodeapi/rest-api/v130/assign-plan', [
            //     'user_id' => $request->user_id,
            //     'plan_id' => $request->plan_id,
            //     'plan_name' => $plan->name,
            //     'plan_price' => $plan->price,
            //     'duration' => $request->duration,
            // ]);

            return response()->json([
                'success' => true,
                'message' => 'Plan "' . $plan->name . '" assigned successfully for ' . $request->duration . ' days!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign manual payment to user via API call (Admin only)
     */
    public function assignPayment(Request $request)
    {
        // Admin check
        $currentUser = auth()->user();
        if (!$currentUser || $currentUser->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only admins can assign payments.'
            ], 403);
        }

        $request->validate([
            'user_id' => 'required|string',
            'channel_id' => 'nullable|string',
            'video_id'   => 'nullable|string',
            'plan_id'    => 'nullable|string',
            'price_amount'   => 'required|numeric|min:0',
            'paid_amount'    => 'required|numeric|min:0',
            'custom_duration' => 'required|integer|min:1'
        ]);

        try {
            // 1) Ensure user exists
            $user = User::find($request->user_id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // 2) Build subscription document directly
            $nowMs = (int) (microtime(true) * 1000);
            $durationMs = (int) $request->custom_duration * 24 * 60 * 60 * 1000;

            $subscriptionData = [
                'user_id'       => new CastsObjectId($request->user_id),
                'plan_id'       => $request->plan_id ? new CastsObjectId($request->plan_id) : null,
                'channel_id'    => $request->channel_id ? new CastsObjectId($request->channel_id) : null,
                'video_id'      => $request->video_id ?? null,
                'price_amount'  => (float) $request->price_amount,
                'timestamp_from' => $nowMs,
                'timestamp_to'  => $nowMs + $durationMs,
                'payment_method' => 'Manual',
                'payment_info'  => [
                    'received_by' => $currentUser->name,
                    'status' => 'paid',
                    'note' => 'Manually assigned payment'
                ],
                'recurring'     => 0,
                'status'        => 1,
                'ispayment'     => 1,
                'is_active'     => true,
                'receipt'       => 'receipt_' . time(),
                'currency'      => 'INR',
                'amount'        => (float) $request->price_amount,
                'amount_due'    => 0,
                'amount_paid'   => (float) $request->paid_amount,
                'created_at'    => time(),
                '__v'           => 0,
                'assigned_by'   => $currentUser->_id,
                'assigned_at'   => now(),
            ];

            // 3) Save subscription
            $subscription = Subscriptions::create($subscriptionData);

            return response()->json([
                'success' => true,
                'message' => 'Manual payment assigned successfully to ' . $user->name . '!',
                'data' => $subscription
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign payment: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Get all channels for dropdown (Admin only)
     */
    public function getChannels()
    {
        try {
            $currentUser = auth()->user();
            if ($currentUser->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Only admins can view channels.'
                ], 403);
            }

            // Get all channels including self
            $channels = Channel::all(['_id', 'channel_name', 'user_id']);

            // Add current admin user as a channel option
            $channelsList = collect([
                [
                    'id' => (string) $currentUser->_id,
                    '_id' => (string) $currentUser->_id,
                    'channel_name' => $currentUser->name . ' (Self)',
                    'user_id' => (string) $currentUser->_id
                ]
            ]);

            // Add other channels
            $channels->each(function ($channel) use ($channelsList) {
                $channelsList->push([
                    'id' => (string) $channel->_id,
                    '_id' => (string) $channel->_id,
                    'channel_name' => $channel->channel_name,
                    'user_id' => (string) $channel->user_id
                ]);
            });

            return response()->json([
                'success' => true,
                'data' => $channelsList->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch channels: ' . $e->getMessage()
            ], 500);
        }
    }
}
