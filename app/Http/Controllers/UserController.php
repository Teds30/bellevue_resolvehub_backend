<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Twilio\Rest\Client;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $res = User::get()->where('d_status', 1);

        if (!$res || !$res->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No users yet."
            ], 404);
        }

        return [
            "data" => $res,
            "success" => true,
        ];
    }



    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'position_id' => 'integer',
            'department_id' => 'integer',
            'phone_number' => 'string',
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $res = User::create($request->all());
        return  response()->json($res, 201);
    }


    public function show($id)
    {
        $res = User::with(['position:id,name', 'department:id,name'])
            ->where('id', $id)
            ->where('d_status', 1)
            ->first();

        if (!$res || !$res->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "User not found."
            ], 404);
        }

        return [
            "data" => $res,
            "success" => true,
        ];
    }


    public function update(Request $request, $id)
    {
        $res = User::find($id);

        if (!$res || !$res->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "User not found."
            ], 404);
        }

        $res->update($request->all());

        return [
            "data" => $res,
            "success" => true,
        ];
    }


    public function destroy($id)
    {
        $res = User::get()->where('id', $id)->first();

        if (!$res || !$res->count()) {
            return response()->json([
                "success" => false,
                "message" => "User not found."
            ], 404);
        }

        $res->delete();

        return [
            "success" => true,
            "message" => "Successfully deleted."
        ];
    }
}
