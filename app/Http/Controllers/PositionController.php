<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Http\Requests\StorePositionRequest;
use App\Http\Requests\UpdatePositionRequest;
use App\Models\Permission;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $res = Position::get()->where('d_status', 1);

        if (!$res || !$res->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No positions yet."
            ], 404);
        }

        return [
            "data" => $res,
            "success" => true,
        ];
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
            'name' => 'required|string',
            'department_id' => 'required|integer',
        ]);
        $res = Position::create($request->all());
        return  response()->json($res, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $res = Position::get()->where('id', $id)->where('d_status', 1)->first();

        if (!$res || !$res->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "Position not found."
            ], 404);
        }

        $res->people;

        return [
            "data" => $res,
            "success" => true,
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Position $position)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $res = Position::find($id);

        if (!$res || !$res->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "Position not found."
            ], 404);
        }

        $res->update($request->all());

        return [
            "data" => $res,
            "success" => true,
        ];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $res = Position::get()->where('id', $id)->first();

        if (!$res || !$res->count()) {
            return response()->json([
                "success" => false,
                "message" => "Position not found."
            ], 404);
        }

        $res->delete();

        return [
            "success" => true,
            "message" => "Successfully deleted."
        ];
    }

    public function permissions($id)
    {

        $res = Position::get()->where('id', $id)->first();

        if (!$res || !$res->count()) {
            return response()->json([
                "success" => false,
                "message" => "Position not found."
            ], 404);
        }

        $permissions = $res->permissions->pluck('access_code')->toArray();

        unset($res['permissions']);

        $res['permissions'] = $permissions;


        return [
            "data" => $res,
            "success" => true,
        ];
    }

    public function update_permissions($id, Request $request)
    {

        $codes = $request['codes'];
        $position = Position::find($id);

        if (!$position) {
            return response()->json([
                "success" => false,
                "message" => "Position not found."
            ], 404);
        }

        // Delete existing permissions for the position
        $position->permissions()->delete();

        // Create new permissions with the provided codes
        $permissions = [];
        foreach ($codes as $code) {
            $permissions[] = new Permission([
                'position_id' => $id,
                'access_code' => $code
            ]);
        }
        $position->permissions()->saveMany($permissions);

        return response()->json([
            "data" => $position->permissions,
            "success" => true,
        ]);
    }
}
