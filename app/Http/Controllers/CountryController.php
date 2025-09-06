<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $countries = Country::orderBy('country')->get();
        return view('country.index', compact('countries'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('country.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'country' => 'required|string|max:100',
            'currency' => 'required|string|max:100',
            'symbol' => 'required|string|max:10',
            'iso_code' => 'required|string|max:3',
            'exchange_rate' => 'required|numeric',
            'status' => 'required|in:0,1',
            'default' => 'required|in:0,1',
        ]);

        Country::create([
            'country' => $request->country,
            'currency' => $request->currency,
            'symbol' => $request->symbol,
            'iso_code' => $request->iso_code,
            'exchange_rate' => $request->exchange_rate,
            'status' => $request->status,
            'default' => $request->default,
        ]);

        return redirect()->route('country.index')->with('success', 'Country added successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $country = Country::findOrFail($id);
        return view('country.edit', compact('country'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'country' => 'required|string|max:100',
            'currency' => 'required|string|max:100',
            'symbol' => 'required|string|max:10',
            'iso_code' => 'required|string|max:3',
            'exchange_rate' => 'required|numeric',
            'status' => 'required|in:0,1',
            'default' => 'required|in:0,1',
        ]);

        $country = Country::findOrFail($id);
        $country->update([
            'country' => $request->country,
            'currency' => $request->currency,
            'symbol' => $request->symbol,
            'iso_code' => $request->iso_code,
            'exchange_rate' => $request->exchange_rate,
            'status' => $request->status,
            'default' => $request->default,
        ]);

        return redirect()->route('countries.index')->with('success', 'Country updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $country = Country::findOrFail($id);
        $country->delete();

        return redirect()->route('country.index')->with('success', 'Country deleted successfully!');
    }
}
