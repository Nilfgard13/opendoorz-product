<?php

namespace App\Http\Controllers\api;

use App\Models\Image;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all(); // Mengambil semua produk

        if ($products->isEmpty()) {
            return response()->json(['message' => 'No products found'], 200);
        }

        return ProductResource::collection($products); // Return dalam bentuk resource
    }

    // public function store(Request $request)
    // {
    //     // Validasi data
    //     $validatedData = $request->validate([
    //         'title' => 'required|string|max:255',
    //         'description' => 'required|string',
    //         'price' => 'required|numeric',
    //         'location' => 'required|string|max:255',
    //         'type' => 'required|string|max:50',
    //         'status' => 'required|in:available,sold',
    //     ]);

    //     // Simpan data ke database
    //     $product = Product::create($validatedData);

    //     // Return respon sukses
    //     return response()->json([
    //         'message' => 'Product created successfully',
    //         'data' => $product
    //     ], 201);
    // }

    public function store(Request $request)
    {
        // Validasi data
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'location' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'status' => 'required|in:available,sold',
            'category_id' => 'required|exists:categories,id'
        ]);

        DB::beginTransaction();

        try {
            // Simpan data ke database tabel `products`
            $product = Product::create($validatedData);

            // Simpan data ke tabel `property_categories`
            ProductCategory::create([
                'property_id' => $product->id,
                'category_id' => $request->input('category_id') // ID kategori yang dipilih oleh user

            ]);

            // Commit transaksi jika semuanya sukses
            DB::commit();

            // Return respon sukses
            return response()->json([
                'message' => 'Product created successfully',
                'data' => $product
            ], 201);
        } catch (\Exception $e) {
            // Rollback jika terjadi error
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function search($title = null, $location = null, $status = null)
    {
        $query = Product::query();

        if ($title) {
            $query->where('title', 'like', '%' . $title . '%');
        }

        if ($location) {
            $query->where('location', 'like', '%' . $location . '%');
        }

        if ($status) {
            $query->where('status', $status);
        }

        $properties = $query->get();

        return response()->json($properties);
    }

    // public function edit(string $id)
    // {
    //     //
    // }

    public function update(Request $request, $id)
    {
        // Validasi Input
        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric',
            'location' => 'sometimes|required|string',
            'type' => 'sometimes|required|string',
            'status' => 'sometimes|required|in:available,sold',
            'category_id' => 'sometimes|required|array',
            'category_id.*' => 'exists:categories,id',
            'images' => 'sometimes|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Cari Properti berdasarkan ID
        $property = Product::findOrFail($id);

        // Update Properti
        $property->update($validatedData);

        // Update Kategori Properti
        if ($request->has('category_id')) {
            $property->category()->sync($request->category_id);
        }

        // Update Gambar Properti
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = $image->store('property_images', 'public');
                Image::create([
                    'product_id' => $property->id,
                    'url' => $imagePath,
                    'alt_text' => $property->title
                ]);
            }
        }

        return response()->json(['message' => 'Property updated successfully', 'data' => $property], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $property = Product::findOrFail($id);

        // Hapus Properti
        $property->delete();

        return response()->json(['message' => 'Property deleted successfully'], 200);
    }
}
