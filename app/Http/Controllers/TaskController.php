<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $res = Task::get()->where('d_status', 1);

        if (!$res || !$res->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No tasks yet."
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
            'room' => 'required|string|max:255',
            'issue_id' => 'required|integer',
            'details' => 'nullable|string',
            'requestor_id' => 'required|integer',
            'department_id' => 'required|integer',
            'pending_marker_id' => 'nullable|integer',
            'completed_marker_id' => 'nullable|integer',
            'pending_reason' => 'nullable|string',
            'action_taken' => 'nullable|string',
            'remarks' => 'nullable|string',
            'assignee_id' => 'nullable|integer',
            'assignor_id' => 'nullable|integer',
            'status' => 'nullable|string',
            'priority' => 'nullable|integer',
            'schedule' => 'nullable|date_format:Y-m-d H:i',
            'd_status' => 'nullable|integer',
        ]);
        $res = Task::create($request->all());
        return  response()->json($res, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $res = Task::get()->where('id', $id)->where('d_status', 1)->first();

        if (!$res || !$res->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "Task not found."
            ], 404);
        }


        $res->issue;
        $res->department;
        $res->requestor->department;
        $res->assignee;
        $res->assignor;
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
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $res = Task::find($id);

        if (!$res || !$res->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "Task not found."
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
        $res = Task::get()->where('id', $id)->first();

        if (!$res || !$res->count()) {
            return response()->json([
                "success" => false,
                "message" => "Task not found."
            ], 404);
        }

        $res->delete();

        return [
            "success" => true,
            "message" => "Successfully deleted."
        ];
    }
}
