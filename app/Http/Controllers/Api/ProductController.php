<?php

namespace App\Http\Controllers\api;

use App\Models\Image;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     $products = Product::with(['categories', 'images'])->get();

    //     if ($products->isEmpty()) {
    //         return response()->json(['message' => 'No products found'], 200);
    //     }

    //     return ProductResource::collection($products); // Return dalam bentuk resource
    // }
    public function index()
    {
        // Mengambil semua produk beserta gambar yang terkait
        $products = Product::with('images')->get();

        return response()->json([
            'message' => 'Product list with images',
            'data' => $products
        ], 200);
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
    //         'category_id' => 'required|exists:categories,id',
    //     ]);

    //     DB::beginTransaction();

    //     try {
    //         // Simpan data ke database tabel `products`
    //         $product = Product::create($validatedData);

    //         // Simpan data ke tabel `property_categories`
    //         ProductCategory::create([
    //             'property_id' => $product->id,
    //             'category_id' => $request->input('category_id') // ID kategori yang dipilih oleh user

    //         ]);

    //         // Commit transaksi jika semuanya sukses
    //         DB::commit();

    //         // Return respon sukses
    //         return response()->json([
    //             'message' => 'Product created successfully',
    //             'data' => $product
    //         ], 201);
    //     } catch (\Exception $e) {
    //         // Rollback jika terjadi error
    //         DB::rollBack();

    //         return response()->json([
    //             'message' => 'Failed to create product',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function store(Request $request)
    {
        try {
            // Validasi data input
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|decimal:0,2',
                'location' => 'required|string|max:255',
                'type' => 'required|string|max:50',
                'status' => 'required|in:available,sold',
                'category_id' => 'required|exists:categories,id',
                'image_path' => 'required|array',
                'image_path.*' => 'file|mimes:jpeg,png,jpg,gif|max:4096'
            ]);

            DB::beginTransaction();

            // Simpan data produk
            $property = Product::create($validatedData);

            // Simpan kategori produk
            ProductCategory::create([
                'property_id' => $property->id,
                'category_id' => $request->input('category_id')
            ]);

            // Simpan gambar
            if ($request->hasFile('image_path')) {
                foreach ($request->file('image_path') as $image) {
                    $path = $image->store('images', 'public');

                    Image::create([
                        'property_id' => $property->id,
                        'image_path' => $path
                    ]);
                }
            } else {
                throw new \Exception("No images found in request");
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Property created successfully',
                'data' => $property->load('images', 'categories')
            ], 201); // Created status code

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422); // Unprocessable Entity

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create property',
                'error' => $e->getMessage()
            ], 500); // Internal Server Error
        }
    }


    // public function uploadImages(Request $request, Product $product)
    // {
    //     $request->validate([
    //         'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:4096',
    //     ]);

    //     $images = $request->file('images');

    //     try {
    //         foreach ($images as $image) {

    //             $path = $image->store('images', 'public');

    //             // Simpan path gambar ke tabel `images`
    //             Image::create([
    //                 'product_id' => $product->id,
    //                 'image_path' => $path
    //             ]);
    //         }

    //         return response()->json([
    //             'message' => 'Images uploaded successfully',
    //             'product' => $product,
    //         ], 201);
    //     } catch (\Exception $e) {
    //         Log::error('Image upload failed: ' . $e->getMessage());
    //         return response()->json([
    //             'message' => 'Image upload failed',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // public function storeImage(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'image_path' => 'required',
    //             'property_id' => 'required|exists:properties,id',
    //         ]);
    //         $imageName = time() . '.' . $request->image->extension();
    //         $imagePath = 'images/' . $imageName;
    //         $request->image->move(public_path('images'), $imageName);

    //         $product = new Product();

    //         $product->image = $imagePath;
    //         $product->image_path = $imagePath; // Store the file path
    //         $product->property_id = $request->property_id; // Set the property_id foreign key
    //         $product->save();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Product image uploaded successfully.',
    //             'data' => [
    //                 'product_id' => $product->id,
    //                 'image_path' => $product->image_path,
    //                 'property_id' => $product->property_id,
    //             ]
    //         ], 201);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed.',
    //             'errors' => $e->errors(),
    //         ], 422);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'An error occurred while processing your request.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

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
