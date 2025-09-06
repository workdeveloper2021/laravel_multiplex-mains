<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $plans = Plan::paginate(10);
        return view('plans.index', compact('plans'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $countries = Country::all()->toArray();
        return view('plans.create', compact('countries'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|unique:plans,plan_id',
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:100',
            'currency' => 'required|string|max:10',
            'day' => 'required|integer|min:1',
            'screens' => 'nullable|integer|min:1',
            'price' => 'required|numeric|min:0',
            'status' => 'required|boolean',
        ]);

        Plan::create($request->all());

        return redirect()->route('plan.index')->with('success', 'Plan created successfully!');
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
    public function edit($id)
    {
        $plan = Plan::findOrFail($id);
        return view('plans.edit', compact('plan'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'plan_id' => "required|unique:plans,plan_id,$id,_id",
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:100',
            'currency' => 'required|string|max:10',
            'day' => 'required|integer|min:1',
            'screens' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'status' => 'required|boolean',
        ]);

        $plan = Plan::findOrFail($id);
        $plan->update($request->all());

        return redirect()->route('plan.index')->with('success', 'Plan updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $plan = Plan::findOrFail($id);
        $plan->delete();

        return redirect()->route('plan.index')->with('success', 'Plan deleted successfully!');
    }

}
