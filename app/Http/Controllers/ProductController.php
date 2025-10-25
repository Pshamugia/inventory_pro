<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ProductController extends Controller
{
    public function index(){
        $products = Product::with(['category','unit'])->latest()->paginate(15);
        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        return view('products.create', compact('categories','units'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sku'           => 'required|string|max:50|unique:products,sku',
            'name'          => 'required|string|max:255',
            'category_id'   => 'required|integer|exists:categories,id',
            'unit_id'       => 'required|integer|exists:units,id',
            'cost_price'    => 'required|numeric|min:0',
            'sale_price'    => 'required|numeric|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'photo'         => 'nullable|image|max:2048',
        ]);

        if (! Schema::hasColumn('products', 'photo')) {
            unset($data['photo']);
        }

        if ($request->hasFile('photo') && Schema::hasColumn('products','photo')) {
            $file     = $request->file('photo');
            $name     = uniqid().'_'.preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $destPath = public_path('uploads/products');
            if (!is_dir($destPath)) { mkdir($destPath, 0775, true); }
            $file->move($destPath, $name);
            $data['photo'] = 'uploads/products/'.$name; // relative to /public
        }

        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Product created!');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        return view('products.edit', compact('product','categories','units'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'sku'           => 'required|string|max:50|unique:products,sku,'.$product->id,
            'name'          => 'required|string|max:255',
            'category_id'   => 'required|integer|exists:categories,id',
            'unit_id'       => 'required|integer|exists:units,id',
            'cost_price'    => 'required|numeric|min:0',
            'sale_price'    => 'required|numeric|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'photo'         => 'nullable|image|max:2048',
        ]);

        if (! Schema::hasColumn('products', 'photo')) {
            unset($data['photo']);
        }

        // Handle optional new image
        if ($request->hasFile('photo') && Schema::hasColumn('products','photo')) {
            // delete old file if exists
            if ($product->photo && file_exists(public_path($product->photo))) {
                @unlink(public_path($product->photo));
            }

            $file     = $request->file('photo');
            $name     = uniqid().'_'.preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $destPath = public_path('uploads/products');
            if (!is_dir($destPath)) { mkdir($destPath, 0775, true); }
            $file->move($destPath, $name);
            $data['photo'] = 'uploads/products/'.$name;
        }

        $product->update($data);

        return redirect()->route('products.index')->with('success', 'Product updated!');
    }

    public function destroy(Product $product)
    {
        // remove image from disk if present
        if (Schema::hasColumn('products','photo') && $product->photo && file_exists(public_path($product->photo))) {
            @unlink(public_path($product->photo));
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted!');
    }
}
