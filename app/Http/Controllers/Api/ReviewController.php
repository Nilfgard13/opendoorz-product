<?php

namespace App\Http\Controllers\api;

use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReviewController extends Controller
{
    public function store(Request $request, $productId)
    {
        // Validasi Input
        $validatedData = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        // Cek apakah produk ada
        $product = Product::findOrFail($productId);

        // Simpan Ulasan
        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $request->user()->id, // Ambil dari pengguna yang sedang login
            'rating' => $validatedData['rating'],
            'comment' => $validatedData['comment'],
        ]);

        return response()->json(['message' => 'Review created successfully', 'data' => $review], 201);
    }

    public function update(Request $request, $reviewId)
    {
        // Validasi Input
        $validatedData = $request->validate([
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'sometimes|required|string',
        ]);

        // Cari ulasan berdasarkan ID
        $review = Review::findOrFail($reviewId);

        // Update Ulasan
        $review->update($validatedData);

        return response()->json(['message' => 'Review updated successfully', 'data' => $review], 200);
    }

    public function destroy($id)
    {
        $review = Review::findOrFail($id);

        // Hapus Ulasan
        $review->delete();

        return response()->json(['message' => 'Review deleted successfully'], 200);
    }

    public function index($productId)
    {
        // Cari semua ulasan untuk produk tertentu
        $reviews = Review::where('product_id', $productId)->get();

        return response()->json($reviews);
    }
}
