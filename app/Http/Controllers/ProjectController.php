<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $res = Project::get()->where('d_status', 1);

        if (!$res || !$res->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No projects yet."
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
            'title' => 'required|string|max:255',
            'details' => 'nullable|string',
            'location' => 'nullable|string',
            'coordinates' => 'nullable|string',
            'schedule' => 'nullable|date',
            'deadline' => 'nullable|date',
            'type' => 'required|integer',
            'requestor_id' => 'required|integer',
            'department_id' => 'required|integer',
            'incharge_id' => 'nullable|integer',
            'pending_marker_id' => 'nullable|integer',
            'completed_marker_id' => 'nullable|integer',
            'pending_reason' => 'nullable|string',
            'remarks' => 'nullable|string',
            'status' => 'nullable|integer',
            'd_status' => 'nullable|integer',
        ]);

        $res = Project::create($request->all());
        return  response()->json($res, 201);
    }


    public function department_projects($id)
    {
        $res = Project::get()->where('department_id', $id)->where('d_status', 1);

        if (!$res || !$res->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No projects yet."
            ], 404);
        }

        return [
            "data" => $res,
            "success" => true,
        ];
    }

    public function user_projects($id)
    {
        $res = Project::get()->where('requestor_id', $id)->where('d_status', 1)->values();

        if (!$res || !$res->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No projects yet."
            ], 404);
        }

        return [
            "data" => $res,
            "success" => true,
        ];
    }
    public function assigned_projects($id)
    {
        $res = Project::get()->where('incharge_id', $id)->where('d_status', 1)->values();

        if (!$res || !$res->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No projects yet."
            ], 404);
        }

        return [
            "data" => $res,
            "success" => true,
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $res = Project::get()->where('id', $id)->where('d_status', 1)->first();

        if (!$res || !$res->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "Project not found."
            ], 404);
        }


        $res->department;
        $res->requestor->department;
        $res->incharge;
        $res->pending_marker;
        $res->completed_marker;

        if ($res->assignee) $res->assignee->department;
        if ($res->assignor) $res->assignor->department;
        if ($res->pending_marker) $res->pending_marker->department;
        if ($res->completed_marker) $res->completed_marker->department;


        return [
            "data" => $res,
            "success" => true,
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $res = Project::find($id);

        if (!$res || !$res->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "Project not found."
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
        $res = Project::get()->where('id', $id)->first();

        if (!$res || !$res->count()) {
            return response()->json([
                "success" => false,
                "message" => "Project not found."
            ], 404);
        }

        $res->delete();

        return [
            "success" => true,
            "message" => "Successfully deleted."
        ];
    }
}
