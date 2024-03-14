<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use function Laravel\Prompts\password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Check if the user with the provided email already exists
        // $userExist = User::where('email', $request['email'])->first();

        // if ($userExist) {
        //     return response([
        //         'message' => 'Email already exist.'
        //     ], 401);
        // }

        // $fields = $request->validate([
        //     'first_name' => 'required|string',
        //     'middle_name' => 'string',
        //     'last_name' => 'required|string',
        //     'gender' => 'required|string',
        //     'phone_number' => 'required|string',
        //     'user_type_id' => 'required|string',
        //     'email' => 'required|string|unique:users,email',
        //     'password' => 'required|string|confirmed',
        // ]);

        // $user = User::create([
        //     'first_name' => $fields['first_name'],
        //     'middle_name' => $fields['middle_name'] ?? '',
        //     'last_name' => $fields['last_name'],
        //     'gender' => $fields['gender'],
        //     'phone_number' => $fields['phone_number'],
        //     'user_type_id' => $fields['user_type_id'],
        //     'email' => $fields['email'],
        //     'password' => $fields['password'],
        // ]);

        // $user = User::find($user->id);

        // $token = $user->createToken('myapptoken')->plainTextToken;

        // $response = [
        //     'user' => $user,
        //     'token' => $token
        // ];

        // return response($response, 201);
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('username', $fields['username'])->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            // return response([
            //     'user' => [],
            //     'message' => 'Invalid Credentials.'
            // ], 401);
            return ['data' => ['user' => null, 'token' => null], 'success' => false, 'message' => 'Incorrect credentials.'];
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = ['data' => [
            'user' => $user,
            'token' => $token
        ], 'success' => true, 'message' => null];

        return response($response, 201);
    }

    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();

        return [
            'message' => 'Logged out'
        ];
    }

    public function updatePassword(Request $request)
    {
        $fields = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        // Check if the user with the provided username exists
        $user = User::where('username', $fields['email'])->first();

        if (!$user) {
            return response([
                'message' => 'Username is not registered.'
            ], 401);
        }

        // Update the user's password
        $user->update([
            'password' => bcrypt($fields['password']) // You should hash the password
        ]);

        // Create a new token
        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = ['data' => [
            'user' => $user,
            'token' => $token
        ], 'success' => true, 'message' => null];

        return response($response, 201);
    }
}