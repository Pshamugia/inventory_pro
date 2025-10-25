<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::latest()->get();
        return view('units.index', compact('units'));
    }

    public function create()
    {
        return view('units.create');
    }

 public function store(Request $request)
{
    $data = $request->validate([
        'name'   => 'required|string|max:100',
        'symbol' => 'nullable|string|max:10',
    ]);

    $unit = \App\Models\Unit::create($data);

    // If it’s an AJAX/JSON request, return the created row as JSON.
    if ($request->expectsJson() || $request->wantsJson()) {
        return response()->json(['ok' => true, 'unit' => $unit]);
    }

    return redirect()->route('units.index')->with('success','Unit added successfully!');
}

    public function destroy(Unit $unit)
    {
        $unit->delete();
        return back()->with('success','Unit deleted.');
    }

    public function quickStore(\Illuminate\Http\Request $request)
{
    $data = $request->validate([
        'name'   => 'required|string|max:100',
        'symbol' => 'nullable|string|max:10',
    ]);
    $unit = \App\Models\Unit::create($data);
    return response()->json(['ok' => true, 'unit' => $unit], 201);
}


public function quickStoreBack(Request $request)
{
    $data = $request->validate([
        'name'   => 'required|string|max:100',
        'symbol' => 'nullable|string|max:10',
    ]);

    // Only include 'symbol' if the column exists in your DB
    $payload = ['name' => $data['name']];
    if (Schema::hasColumn('units', 'symbol') && !empty($data['symbol'])) {
        $payload['symbol'] = $data['symbol'];
    }

    $unit = Unit::create($payload);

    return back()
        ->with('ok', 'Unit added')
        ->with('new_unit_id', $unit->id);
}



}
