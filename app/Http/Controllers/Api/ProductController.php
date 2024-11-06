<?php

namespace App\Http\Controllers\api;

use App\Models\Image;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index()
    {
        // Mengambil semua produk beserta gambar yang terkait
        $products = Product::with('images')->get();

        return response()->json([
            'message' => 'Product list with images',
            'data' => $products
        ], 200);
    }

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
        $query = Product::with('images'); // Memuat relasi images

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

        return response()->json($properties, 200, [], JSON_PRETTY_PRINT);
    }


    // public function update(Request $request, $id)
    // {
    //     // Validasi Input
    //     $validatedData = $request->validate([
    //         'title' => 'required|string|max:255',
    // 'description' => 'required|string',
    // 'price' => 'required|numeric|decimal:0,2',
    // 'location' => 'required|string|max:255',
    // 'type' => 'required|string|max:50',
    // 'status' => 'required|in:available,sold',
    // 'category_id' => 'required|exists:categories,id',
    // 'image_path' => 'required|array',
    // 'image_path.*' => 'file|mimes:jpeg,png,jpg,gif|max:4096'
    //     ]);

    //     // Cari Properti berdasarkan ID
    //     $property = Product::findOrFail($id);

    //     // Update Properti
    //     $property->update($validatedData);

    //     // Update Kategori Properti
    //     if ($request->has('category_id')) {
    //         $property->category()->sync($request->category_id);
    //     }

    //     // Update Gambar Properti
    //     if ($request->hasFile('images')) {
    //         foreach ($request->file('images') as $image) {
    //             $imagePath = $image->store('property_images', 'public');
    //             Image::create([
    //                 'product_id' => $property->id,
    //                 'url' => $imagePath,
    //                 'alt_text' => $property->title
    //             ]);
    //         }
    //     }

    //     return response()->json(['message' => 'Property updated successfully', 'data' => $property], 200);
    // }

    public function update(Request $request, $id)
    {
        try {
            // Cek apakah property ada
            $property = Product::findOrFail($id);

            // Validasi yang lebih fleksibel untuk update
            $validatedData = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'price' => 'sometimes|required|numeric|decimal:0,2',
                'location' => 'sometimes|required|string|max:255',
                'type' => 'sometimes|required|string|max:50',
                'status' => 'sometimes|required|in:available,sold',
                'category_id' => 'sometimes|required|exists:categories,id',
                'image_path' => 'sometimes|array',
                'image_path.*' => 'sometimes|file|mimes:jpeg,png,jpg,gif|max:4096'
            ]);

            DB::beginTransaction();

            // Update hanya field yang ada dalam request
            if ($request->has('title')) $property->title = $validatedData['title'];
            if ($request->has('description')) $property->description = $validatedData['description'];
            if ($request->has('price')) $property->price = $validatedData['price'];
            if ($request->has('location')) $property->location = $validatedData['location'];
            if ($request->has('type')) $property->type = $validatedData['type'];
            if ($request->has('status')) $property->status = $validatedData['status'];

            $property->save();

            // Update category jika ada
            if ($request->has('category_id')) {
                ProductCategory::where('property_id', $id)->delete();
                ProductCategory::create([
                    'property_id' => $id,
                    'category_id' => $validatedData['category_id']
                ]);
            }

            // Update images jika ada
            if ($request->hasFile('image_path')) {
                // Hapus gambar lama
                foreach ($property->images as $oldImage) {
                    Storage::disk('public')->delete($oldImage->image_path);
                    $oldImage->delete();
                }

                // Upload gambar baru
                foreach ($request->file('image_path') as $image) {
                    $path = $image->store('images', 'public');
                    Image::create([
                        'property_id' => $id,
                        'image_path' => $path
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Property updated successfully',
                'data' => $property->load('images', 'categories')
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'request_data' => $request->all() // Untuk debugging
            ], 422);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Property not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update property',
                'error' => $e->getMessage()
            ], 500);
        }
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
