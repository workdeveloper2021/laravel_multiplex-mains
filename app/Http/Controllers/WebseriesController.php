<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Country;
use App\Models\Genre;
use App\Models\Languages;
use App\Models\Seasons;
use App\Models\WebSeries;
use Illuminate\Http\Request;

class WebseriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ensure only admin can access webseries
        $user = auth()->user();
        if ($user->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Access denied. Webseries management is for admins only.');
        }

        $webseries = WebSeries::all();
        return view('webseries.index', compact('webseries'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $genres = Genre::all();
        $channels = Channel::all()->toArray();
        $languages = Languages::all()->toArray();
        $countries = Country::all()->toArray();

        return view('webseries.create',compact('genres', 'channels', 'languages', 'countries'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'genre' => 'required|array|min:1',
            'genre.*' => 'string',
            'pricing' => 'nullable|array',
            'channel_id' => 'nullable|string',
            'release' => 'nullable|date',
            'price' => 'nullable|numeric|min:0',
            'language' => 'required|array|min:1',
            'language.*' => 'required|integer|between:1,14',
            'country' => 'nullable|array',
            'country.*' => 'string',
            'is_paid' => 'nullable|boolean',
            'publication' => 'nullable|boolean',
            'trailer_link' => 'nullable|url',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'enable_download' => 'nullable|boolean',
        ]);

        $isPaid = $request->is_paid ? true : false;
        $price = $isPaid ? ($request->price ?? 0) : 0;

        // Check user role and set channel accordingly
        $user = auth()->user();
        $isChannel = $user->role === 'channel' ? true : false;

        // Set channel_id based on user role
        $channelId = $user->role === 'admin' ? $request->channel_id : $user->_id;
        if ($channelId && is_string($channelId)) {
            $channelId = new \MongoDB\BSON\ObjectId($channelId);
        }

        try {
            // Handle pricing data
            $pricingData = [];
            if (is_null($request->price) && $request->has('prices') && is_array($request->prices)) {
                $countryIds = array_keys($request->prices);
                $objectIds = array_map(fn($id) => new \MongoDB\BSON\ObjectId($id), $countryIds);

                $countryRecords = \App\Models\Country::whereIn('_id', $objectIds)->get(['_id', 'iso_code']);

                $countries = [];
                foreach ($countryRecords as $record) {
                    $countries[(string)$record->_id] = $record->iso_code;
                }

                foreach ($request->prices as $countryId => $price) {
                    $currencyCode = $countries[$countryId] ?? null;
                    if ($currencyCode && !empty($price)) {
                        $pricingData[] = [
                            'country' => $currencyCode,
                            'price' => (int) $price,
                            '_id' => new \MongoDB\BSON\ObjectId()
                        ];
                    }
                }
            }

            $websSeriesData = [
                'title' => $request->title,
                'description' => $request->description ?? '',
                'country' => $request->country ?? [],
                'genre' => $request->genre,
                'language' => $request->language ?? [],
                'pricing' => $pricingData,
                'channel_id' => $channelId,
                'release' => $request->release,
                'price' => (int) $price,
                'is_paid' => $isPaid ? 1 : 0,
                'publication' => $request->publication ? true : false,
                'enable_download' => $request->enable_download ? '1' : '0',
                'isChannel' => $isChannel ? 'true' : 'false',
                'seasonsId' => [],
                'createdAt' => now(),
                'updatedAt' => now(),
            ];

            if ($request->trailer_link) {
                $websSeriesData['trailer'] = $request->trailer_link;
            }

            // Handle file uploads (thumbnail, poster)
            $urlFiles = ['thumbnail', 'poster'];
            foreach ($urlFiles as $fileField) {
                if ($request->hasFile($fileField)) {
                    $filename = time() . '_' . $request->file($fileField)->getClientOriginalName();
                    $path = $request->file($fileField)->storeAs('webseries', $filename, 'public');
                    $url = asset('storage/' . $path);
                    $websSeriesData[$fileField . '_url'] = $url;
                }
            }

            // Save to MongoDB
            $webSeries = new WebSeries($websSeriesData);
            $webSeries->save();

            return redirect()->route('content.webseries.index')->with('success', 'Web Series created successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create web series: ' . $e->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $webseries = WebSeries::findOrFail($id);
        return view('webseries.show', compact('webseries'));
    }

    /**
     * Show the form for editing the specified resource.
     * This will redirect to seasons management for the webseries
     */
    public function edit($id)
    {
        // Instead of showing edit form, redirect to seasons management
        return redirect()->route('content.webseries.seasons.index', $id);
    }

    /**
     * Show actual edit form for webseries
     */
    public function editWebseries($id)
    {
        $webseries = WebSeries::findOrFail($id);
        $genres = Genre::all();
        $channels = Channel::all()->toArray();
        $languages = Languages::all()->toArray();
        $countries = Country::all()->toArray();

        // Debug: Check if current user is channel user and webseries belongs to them
        $user = auth()->user();
        if ($user->role === 'channel') {
            // For channel users, ensure they can only edit their own webseries
            if ((string)$webseries->channel_id !== (string)$user->_id) {
                return redirect()->route('content.webseries.index')->with('error', 'You can only edit your own web series.');
            }
        }

        return view('webseries.edit', compact('webseries', 'genres', 'channels', 'languages', 'countries'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $webseries = WebSeries::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'genre' => 'required|array|min:1',
            'genre.*' => 'string',
            'pricing' => 'nullable|array',
            'channel_id' => 'nullable|string',
            'release' => 'nullable|date',
            'price' => 'nullable|numeric|min:0',
            'language' => 'required|array|min:1',
            'language.*' => 'required|integer|between:1,14',
            'country' => 'nullable|array',
            'country.*' => 'string',
            'is_paid' => 'nullable|boolean',
            'publication' => 'nullable|boolean',
            'trailer_link' => 'nullable|url',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'enable_download' => 'nullable|boolean',
        ]);

        $isPaid = $request->is_paid ? true : false;
        $price = $isPaid ? ($request->price ?? 0) : 0;

        // Check user role and set channel accordingly
        $user = auth()->user();
        $isChannel = $user->role === 'channel' ? true : false;

        // Set channel_id based on user role
        $channelId = $user->role === 'admin' ? $request->channel_id : $user->_id;
        if ($channelId && is_string($channelId)) {
            $channelId = new \MongoDB\BSON\ObjectId($channelId);
        }

        try {
            // Handle pricing data
            $pricingData = [];
            if (is_null($request->price) && $request->has('prices') && is_array($request->prices)) {
                $countryIds = array_keys($request->prices);
                $objectIds = array_map(fn($id) => new \MongoDB\BSON\ObjectId($id), $countryIds);

                $countryRecords = \App\Models\Country::whereIn('_id', $objectIds)->get(['_id', 'iso_code']);

                $countries = [];
                foreach ($countryRecords as $record) {
                    $countries[(string)$record->_id] = $record->iso_code;
                }

                foreach ($request->prices as $countryId => $price) {
                    $currencyCode = $countries[$countryId] ?? null;
                    if ($currencyCode && !empty($price)) {
                        $pricingData[] = [
                            'country' => $currencyCode,
                            'price' => (int) $price,
                            '_id' => new \MongoDB\BSON\ObjectId()
                        ];
                    }
                }
            }

            // Update basic fields
            $webseries->title = $request->title;
            $webseries->description = $request->description ?? '';
            $webseries->country = $request->country ?? [];
            $webseries->genre = $request->genre;
            $webseries->language = $request->language ?? [];
            $webseries->pricing = $pricingData;
            $webseries->channel_id = $channelId;
            $webseries->release = $request->release;
            $webseries->price = (int) $price;
            $webseries->is_paid = $isPaid ? 1 : 0;
            $webseries->publication = $request->publication ? true : false;
            $webseries->enable_download = $request->enable_download ? '1' : '0';
            $webseries->isChannel = $isChannel ? 'true' : 'false';
            $webseries->updatedAt = now();

            if ($request->trailer_link) {
                $webseries->trailer = $request->trailer_link;
            }

            // Handle file uploads (thumbnail, poster)
            $urlFiles = ['thumbnail', 'poster'];
            foreach ($urlFiles as $fileField) {
                if ($request->hasFile($fileField)) {
                    $filename = time() . '_' . $request->file($fileField)->getClientOriginalName();
                    $path = $request->file($fileField)->storeAs('webseries', $filename, 'public');
                    $url = asset('storage/' . $path);
                    $webseries->{$fileField . '_url'} = $url;
                }
            }

            $webseries->save();

            return redirect()->route('content.webseries.index')->with('success', 'Web Series updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update web series: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $webseries = WebSeries::findOrFail($id);
        $webseries->delete();
        return redirect()->route('content.webseries.index')->with('success', 'Web Series deleted successfully.');
    }
}
