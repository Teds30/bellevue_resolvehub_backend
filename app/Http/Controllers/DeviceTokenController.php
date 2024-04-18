<?php

namespace App\Http\Controllers;

use App\Models\DeviceToken;
use App\Http\Requests\StoreDeviceTokenRequest;
use App\Http\Requests\UpdateDeviceTokenRequest;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'token' => 'required|string',
        ]);

        $res = DeviceToken::get()->where('token', $request->token)->first();

        if ($res) {
            return response()->json([], 201);;
        }

        $res2 = DeviceToken::create($request->all());
        return response()->json($res2, 201);
    }

    /**
     * Display the specified resource.
     */
    public function findToken($token)
    {
        $res = DeviceToken::get()->where('token', $token)->first();

        if (!$res || !$res->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "Task not found."
            ], 404);
        }

        return [
            "data" => $res,
            "success" => true,
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DeviceToken $deviceToken)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDeviceTokenRequest $request, DeviceToken $deviceToken)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $res = DeviceToken::get()->where('token', $request->token)->first();

        if (!$res || !$res->count()) {
            return response()->json([
                "success" => false,
                "message" => "Token not found."
            ], 404);
        }

        $res->delete();

        return [
            "success" => true,
            "message" => "Successfully deleted."
        ];
    }
}
