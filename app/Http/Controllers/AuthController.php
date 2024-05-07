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
        $userExist = User::where('username', $request['username'])->first();

        if ($userExist) {
            return response([
                'message' => 'User already exist.'
            ], 401);
        }


        $fields = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'position_id' => 'required|integer',
            'phone_number' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
        ]);


        $user = User::create([
            'first_name' => $fields['first_name'],
            'last_name' => $fields['last_name'],
            'position_id' => $fields['position_id'],
            'phone_number' => $fields['phone_number'],
            'username' => $fields['username'],
            'password' => $fields['password'],
        ]);

        $user = User::find($user->id);

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = ['data' => [
            'user' => $user,
            'token' => $token
        ], 'success' => true, 'message' => null];

        return response($response, 201);
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('username', $fields['username'])->where('d_status', 1)->first();

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
            'id' => 'required|integer',
            'password' => 'required|string'
        ]);

        $user = User::where('id', $fields['id'])->get()->first();


        if (!$user) {
            return response([
                'message' => 'Username is not registered.'
            ], 401);
        }

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
