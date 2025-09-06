<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MongoDB\BSON\ObjectId;

class ChannelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currentUser = Auth::user();

        // Build query based on user role
        if ($currentUser->role === 'admin' && !$this->isSuperAdmin($currentUser)) {
            // Specific admin - show only their managed channels
            $channels = Channel::all();
        } else {
            // For super admin or other roles, show all channels
            $channels = Channel::all();
        }

        $channels = $channels->map(function ($channel) {
            return [
                'id' => (string) $channel->_id,
                'channel_name' => $channel->channel_name,
                'user' => $channel->user?->name ?? 'Unknown',
                'mobile_number' => $channel->mobile_number,
                'address' => $channel->address,
                'document_path' => $channel->document_path,
                'organization_name' => $channel->organization_name,
                'organization_address' => $channel->organization_address,
                'status' => $channel->status,
                'join_date' => $channel->join_date,
                'last_login' => $channel->last_login,
            ];
        });

        return view('channels.index', compact('channels'));
    }

    public function approve()
    {
        $channels = Channel::where('status', 'approve')->get()->map(function ($channel) {
            return [
                'id' => (string) $channel->_id,
                'channel_name' => $channel->channel_name,
                'user' => $channel->user?->name ?? 'Unknown',
                'mobile_number' => $channel->mobile_number,
                'address' => $channel->address,
                'organization_name' => $channel->organization_name,
                'organization_address' => $channel->organization_address,
                'status' => $channel->status,
                'join_date' => $channel->join_date,
                'last_login' => $channel->last_login,
            ];
        });

        return view('channels.channel_status', ['channels' => $channels, 'title' => 'Approved Channels']);
    }

    public function pendingVideos()
    {
        $user = Auth::user();
        $query = Movie::where('status', 'pending');

        // If channel user, filter by their channel ID
        if ($user->role === 'channel') {
            $channelId = new ObjectId($user->_id);
            $query->where('channel_id', $channelId);
        }

        $videos = $query->get()->map(function ($video) {
            return [
                'id' => (string) $video->_id,
                'title' => $video->title,
                'channel_name' => $video->channel->channel_name ?? 'Unknown',
                'status' => $video->status,
                'uploaded_at' => $video->created_at->format('d-m-Y H:i'),
            ];
        });
//        dd($videos);
        return view('channels.videos_list', ['videos' => $videos, 'title' => 'Pending Channel Videos']);
    }

    public function rejectedVideos()
    {
        $user = Auth::user();
        $query = Movie::where('status', 'rejected');

        // If channel user, filter by their channel ID
        if ($user->role === 'channel') {
            $channelId = new ObjectId($user->_id);
            $query->where('channel_id', $channelId);
        }

        $videos = $query->get()->map(function ($video) {
            return [
                'id' => (string) $video->_id,
                'title' => $video->title,
                'channel_name' => $video->channel->channel_name ?? 'Unknown',
                'status' => $video->status,
                'uploaded_at' => $video->created_at->format('d-m-Y H:i'),
            ];
        });

        return view('channels.videos_list', ['videos' => $videos, 'title' => 'Rejected Channel Videos']);
    }

    public function blockedVideos()
    {
        $user = Auth::user();
        $query = Movie::where('status', 'block');

        // If channel user, filter by their channel ID
        if ($user->role === 'channel') {
            $channelId = new ObjectId($user->_id);
            $query->where('channel_id', $channelId);
        }

        $videos = $query->get()->map(function ($video) {
            return [
                'id' => (string) $video->_id,
                'title' => $video->title,
                'channel_name' => $video->channel->channel_name ?? 'Unknown',
                'status' => $video->status,
                'uploaded_at' => $video->created_at->format('d-m-Y H:i'),
            ];
        });

        return view('channels.videos_list', ['videos' => $videos, 'title' => 'Blocked Channel Videos']);
    }

    public function allVideos()
    {
        $user = Auth::user();

        // If channel user, only show their videos
        if ($user->role === 'channel') {
            $channelId = new ObjectId($user->_id);
            $videos = Movie::where('channel_id', $channelId)->get()->map(function ($video) {
                return [
                    'id' => (string) $video->_id,
                    'title' => $video->title,
                    'channel_name' => $video->channel->channel_name ?? 'My Channel',
                    'status' => $video->status,
                    'uploaded_at' => $video->created_at ? $video->created_at->format('d-m-Y H:i') : 'N/A',
                ];
            });
        } else {
            // For admin, show all videos
            $videos = Movie::all()->map(function ($video) {
            return [
                'id' => (string) $video->_id,
                'title' => $video->title,
                'channel_name' => $video->channel->channel_name ?? 'Unknown',
                'status' => $video->status,
                'uploaded_at' => $video->created_at ? $video->created_at->format('d-m-Y H:i') : 'N/A',
            ];
            });
        }

        return view('channels.videos_list', ['videos' => $videos, 'title' => 'All Channel Videos']);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $channel = Channel::findOrFail($id);

        return view('channels.show', compact('channel'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $channel = Channel::findOrFail($id);

        return view('channels.edit', compact('channel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approve,rejected,block',
        ]);

        $channel = Channel::findOrFail($id);
        $channel->status = $request->input('status');
        $channel->save();

        return redirect()->route('channels.index')->with('success', 'Channel status updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $channel = Channel::findOrFail($id);
        $channel->delete();

        return redirect()->route('channels.index')->with('success', 'Channel deleted successfully.');
    }

    /**
     * Check if the user is a super admin with full access
     */
    private function isSuperAdmin($user)
    {
        return $user->email === 'admin@multiplexplay.com' ||
               $user->is_super_admin === true ||
               $user->admin_level === 'super' ||
               in_array($user->email, [
                   'superadmin@multiplexplay.com',
                   'admin@example.com',
               ]);
    }
}
