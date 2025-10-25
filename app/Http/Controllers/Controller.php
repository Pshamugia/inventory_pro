<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function quickStore(\Illuminate\Http\Request $request)
{
    $data = $request->validate(['name' => 'required|string|max:100']);
    $category = \App\Models\Category::create($data);
    return response()->json(['ok' => true, 'category' => $category], 201);
}

}
