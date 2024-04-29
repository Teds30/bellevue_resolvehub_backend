<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Position;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $res = Department::get()->where('d_status', 1);

        if (!$res || !$res->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No departments yet."
            ], 404);
        }

        foreach ($res as $department) {
            // Count employees for the current department
            $employeeCount = User::whereHas('position', function ($query) use ($department) {
                $query->where('department_id', $department->id);
            })->where('d_status', 1)->count();

            // Append employee count to the department object
            $department->employee_count = $employeeCount;
        }


        return [
            "data" => $res,
            "success" => true,
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);
        $res = Department::create($request->all());
        return  response()->json($res, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $res = Department::get()->where('id', $id)->where('d_status', 1)->first();

        if (!$res || !$res->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "Department not found."
            ], 404);
        }

        $res->positions;


        // Count employees for the current department
        $employeeCount = User::whereHas('position', function ($query) use ($res) {
            $query->where('department_id', $res->id);
        })->where('d_status', 1)->count();

        // Append employee count to the department object
        $res->employee_count = $employeeCount;

        return [
            "data" => $res,
            "success" => true,
        ];
    }


    public function department_employees($id)
    {
        $positions = Position::get()->where('department_id', $id)->values();

        // Extract the position IDs
        $positionIds = $positions->pluck('id')->toArray();
        $res = User::whereHas('position', function ($query) use ($positionIds) {
            $query->whereIn('id', $positionIds);
        })->where('d_status', 1)->get();


        if (!$res || !$res->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No employees found in this department."
            ], 404);
        }

        foreach ($res as $user) {

            $user->position->department;
        }

        return [
            "data" => $res,
            "success" => true,
        ];
    }


    public function department_positions($id)
    {
        $res = Position::get()->where('department_id', $id)->values();



        if (!$res || !$res->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No positions found in this department."
            ]);
        }

        foreach ($res as $user) {

            $user->position;
        }

        return [
            "data" => $res,
            "success" => true,
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $res = Department::find($id);

        if (!$res || !$res->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "Department not found."
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
        $res = Department::get()->where('id', $id)->first();

        if (!$res || !$res->count()) {
            return response()->json([
                "success" => false,
                "message" => "Department not found."
            ], 404);
        }

        $res->delete();

        return [
            "success" => true,
            "message" => "Successfully deleted."
        ];
    }



    public function department_assigned_tasks($department_id)
    {

        $res2 = Task::where('department_id', $department_id)
            ->where('schedule', null)
            ->where('assignee_id', null)
            ->where('completed_marker_id', null)
            ->orderBy('created_at', 'desc')
            ->get();


        if (!$res2 || !$res2->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No assigned tasks found for this department."
            ], 404);
        }


        foreach ($res2 as $task) {
            $task->requestor;
            // $task->issue;
        }


        return [
            "data" => $res2,
            "success" => true,
        ];
    }


    public function department_ongoing_tasks($department_id)
    {

        $res2 = Task::where('department_id', $department_id)
            ->whereDate('schedule', '<=', Carbon::today())
            ->where('completed_marker_id', null)
            ->where('d_status', 1)
            ->orderBy('created_at', 'desc')
            ->get()
            ->values();


        if (!$res2 || !$res2->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No On-Going tasks found for this department."
            ], 404);
        }

        foreach ($res2 as $task) {
            $task->assignee;
            $task->requestor;
            // $task->issue;
        }

        return [
            "data" => $res2,
            "success" => true,
        ];
    }


    public function department_pending_tasks($department_id)
    {

        $res2 = Task::where('department_id', $department_id)
            ->whereDate('schedule', '>', Carbon::today())
            ->where('completed_marker_id', null)
            ->where('d_status', 1)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->values();


        if (!$res2 || !$res2->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No pending tasks found for this department."
            ], 404);
        }

        foreach ($res2 as $task) {
            $task->requestor;
            $task->assignee;
            // $task->issue;
        }

        return [
            "data" => $res2,
            "success" => true,
        ];
    }

    public function department_done_tasks($department_id, Request $request)
    {

        $today = $request->today ?? false;

        $tmp = Task::where('department_id', $department_id)
            // ->whereDate('schedule', '>', Carbon::today())
            ->where('completed_marker_id', '!=', null)
            ->where('d_status', 1)
            ->orderBy('updated_at', 'desc');

        if ($today) {
            $tmp = $tmp->whereDate('updated_at', '=', Carbon::today());
        }

        $res2 = $tmp->get()
            ->values();


        if (!$res2 || !$res2->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No accomplished tasks found for this department."
            ], 404);
        }

        foreach ($res2 as $task) {
            $task->requestor;
            $task->assignee;
            // $task->issue;
        }

        return [
            "data" => $res2,
            "success" => true,
        ];
    }
}
