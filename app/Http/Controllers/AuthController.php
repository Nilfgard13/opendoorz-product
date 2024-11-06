<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function registerUser(Request $request)
    {
        $datauser = new User();
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'role' => ''
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'proses validasi gagal',
                'data' => $validator->errors(),
            ], 401);
        }

        $datauser->name = $request->name;
        $datauser->email = $request->email;
        $datauser->password = Hash::make($request->password);
        $datauser->role = 'user';
        $datauser->save();

        return response()->json([
            'status' => true,
            'message' => 'berhasil memasukkan data baru'
        ], 200);
    }

    public function loginUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'data incorect'
            ], 401);
        }

        $token = $user->createToken($user->name, ['*'], now()->addMinutes(180))->plainTextToken;

        session(['auth_token' => $token]);

        return response()->json([
            'message' => 'Login Succesfull',
            'token_type' => 'Bearer',
            'token' => $token,
            'user' => $user
        ], 200);
    }

    public function getUserData(Request $request)
    {
        $user = $request->user();
        $tokenData = $user->currentAccessToken();

        return response()->json([
            'status' => true,
            'message' => 'Data user berhasil diambil',
            'data' => [
                'user' => $user,
                'token_name' => $tokenData->name,
                'token_abilities' => $tokenData->abilities
            ]
        ]);
    }
}
