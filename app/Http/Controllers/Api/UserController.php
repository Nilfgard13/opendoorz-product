<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        // dd($users);

        return response()->json([
            'status' => 'success',
            'message' => 'Users retrieved successfully',
            'total_users' => $users->count(),  // Menambahkan jumlah total pengguna
            'data' => $users  // Mengembalikan semua data pengguna
        ], 200);
    }


    // Store a new user
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:super admin,admin,user',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => [
                'user' => $user,
            ],
        ], 201);
    }

    // Get a single user
    public function show($search)
    {
        $results = User::where('name', 'LIKE', "%{$search}%")
            ->orWhere('email', 'LIKE', "%{$search}%")
            ->orWhere('role', 'LIKE', "%{$search}%")
            ->get();

        // Cek apakah hasil ditemukan atau tidak
        if ($results->isEmpty()) {
            // Mengembalikan respons jika tidak ada hasil, dengan status 404 (Not Found)
            return response()->json([
                'message' => 'No results found',
                'results' => []
            ], 404);
        }

        // Jika ada hasil, kembalikan respons dengan status 200 (OK)
        return response()->json([
            'message' => 'Search results found',
            'results' => $results
        ], 200);
    }

    // Update a user
    public function update(Request $request, $id)
    {
        // Find the user
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Validate input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|required|string|min:6',
            'role' => 'required|in:super admin,admin,user',
        ]);

        // Update user data
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        // Update password if provided
        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        // Save changes
        $user->save();

        // Response
        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully',
            'data' => [
                'user' => $user,
            ],
        ], 200);
    }
    // Delete a user
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}
