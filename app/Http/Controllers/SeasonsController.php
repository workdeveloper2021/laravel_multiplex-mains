<?php

namespace App\Http\Controllers;

use App\Models\Seasons;
use App\Models\WebSeries;
use Illuminate\Http\Request;
use MongoDB\BSON\ObjectId;

class SeasonsController extends Controller
{
    /**
     * Display seasons for a specific webseries
     */
    public function index($webseriesId)
    {
        $webseries = WebSeries::findOrFail($webseriesId);

        // Get seasons for this webseries
        $seasons = Seasons::where('webSeries', new ObjectId($webseriesId))
                          ->orderBy('createdAt', 'desc')
                          ->get();

        return view('webseries.seasons.index', compact('webseries', 'seasons'));
    }

    /**
     * Show the form for creating a new season
     */
    public function create($webseriesId)
    {
        $webseries = WebSeries::findOrFail($webseriesId);

        // Calculate next season number
        $lastSeason = Seasons::where('webSeries', new ObjectId($webseriesId))
                            ->orderBy('season_number', 'desc')
                            ->first();

        $nextSeasonNumber = $lastSeason ? $lastSeason->season_number + 1 : 1;

        return view('webseries.seasons.create', compact('webseries', 'nextSeasonNumber'));
    }

    /**
     * Store a newly created season
     */
    public function store(Request $request, $webseriesId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'season_number' => 'required|integer|min:1'
        ]);

        $webseries = WebSeries::findOrFail($webseriesId);

        // Create new season
        $season = new Seasons();
        $season->title = $request->title;
        $season->season_number = $request->season_number;
        $season->webSeries = new ObjectId($webseriesId);
        $season->episodesId = [];
        $season->save();

        // Update webseries to include this season
        $webseries->push('seasonsId', $season->_id);

        return redirect()->route('content.webseries.seasons.index', $webseriesId)
                        ->with('success', 'Season created successfully.');
    }

    /**
     * Show the form for editing the specified season
     */
    public function edit($seasonId)
    {
        $season = Seasons::findOrFail($seasonId);
        $webseries = WebSeries::findOrFail($season->webSeries);

        return view('webseries.seasons.edit', compact('season', 'webseries'));
    }

    /**
     * Update the specified season
     */
    public function update(Request $request, $seasonId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'season_number' => 'required|integer|min:1'
        ]);

        $season = Seasons::findOrFail($seasonId);
        $season->title = $request->title;
        $season->season_number = $request->season_number;
        $season->save();

        return redirect()->route('content.webseries.seasons.index', $season->webSeries)
                        ->with('success', 'Season updated successfully.');
    }

    /**
     * Remove the specified season
     */
    public function destroy($seasonId)
    {
        $season = Seasons::findOrFail($seasonId);
        $webseriesId = $season->webSeries;

        // Remove season from webseries
        $webseries = WebSeries::findOrFail($webseriesId);
        $webseries->pull('seasonsId', $season->_id);

        // Delete all episodes of this season
        // This will be handled by Episode model relationships

        $season->delete();

        return redirect()->route('content.webseries.seasons.index', $webseriesId)
                        ->with('success', 'Season deleted successfully.');
    }
}
