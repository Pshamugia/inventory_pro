<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::latest()->get();
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
{
    $data = $request->validate([
        'name' => 'required|string|max:100'
    ]);

    $category = \App\Models\Category::create($data);

    if ($request->expectsJson() || $request->wantsJson()) {
        return response()->json(['ok' => true, 'category' => $category]);
    }

    return redirect()->route('categories.index')->with('success', 'Category added!');
}



    public function destroy(Category $category)
    {
        $category->delete();
        return back()->with('success', 'Category deleted.');
    }



    public function quickStoreBack(\Illuminate\Http\Request $request)
{
    $data = $request->validate(['name' => 'required|string|max:100']);
    $category = \App\Models\Category::create($data);
    // redirect back to product form and remember the new ID
    return back()->with('ok', 'Category added')->with('new_category_id', $category->id);
}

}
