<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Position;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $date = Carbon::today('Asia/Manila')->toDateString();

        $res2 = Task::where('department_id', $department_id)
            ->whereDate('schedule', '>', Carbon::today())
            ->where('completed_marker_id', null)
            ->where('d_status', 1)
            ->orderBy('updated_at', 'desc')
            ->get();


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

    public function department_done_tasks(Request $request, $id,)
    {

        $month = $request->input('month', null);
        $year = $request->input('year', null);
        $custom = $request->input('custom', null);
        $filterBy = $request->input('filter_by', null);

        $res2 = Task::where('department_id', $id)
            // ->whereDate('schedule', '>', Carbon::today())
            ->where('completed_marker_id', '!=', null)
            ->where('status', 4)
            ->where('d_status', 1)
            ->orderBy('updated_at', 'desc');



        $startDate = now()->startOfWeek()->toDateTimeString(); // Start of the current week
        $endDate = now()->endOfWeek()->toDateTimeString(); // End of the current week

        if ($filterBy == 'daily') {
            $res2 = $res2->whereDate('updated_at', Carbon::today())->orderBy('updated_at', 'desc')->get();
        }
        if ($filterBy == 'weekly') {
            $res2 = $res2->whereBetween('updated_at', [$startDate, $endDate])->orderBy('updated_at', 'desc')->get();
        }
        if ($filterBy == 'month' && $month && $year) {
            $res2 = $res2->whereMonth('updated_at', Carbon::parse($month))->whereYear('updated_at', $year)->orderBy('updated_at', 'desc')->get();
        }
        if ($filterBy == 'year' && $year) {
            $res2 = $res2->whereYear('updated_at', $year)->orderBy('updated_at', 'desc')->get();
        }
        if ($filterBy == 'custom' && $custom) {
            $res2 = $res2->whereDate('updated_at', Carbon::parse($custom)->format('Y-m-d'))->get();
        }
        // if ($today) {
        //     $tmp = $tmp->whereDate('updated_at', Carbon::today());
        // }

        // $res2 = $tmp->get()
        //     ->values();


        if (!$res2 || !$res2->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No accomplished tasks found for this department."
            ], 404);
        }


        $groupedTasks = $res2->groupBy(function ($task) {
            return $task->updated_at->format('Y-m-d'); // Grouping tasks by date
        });


        $groupedTasks->transform(function ($tasks) {
            foreach ($tasks as $task) {
                // Load related models if needed
                $task->load('assignee', 'requestor');
            }
            return $tasks;
        });

        // foreach ($res2 as $task) {
        //     $task->requestor;
        //     $task->assignee;
        //     // $task->issue;
        // }

        return [
            "data" => $groupedTasks,
            "success" => true,
        ];
    }

    public function department_cancelled_tasks(Request $request, $id)
    {

        $month = $request->input('month', null);
        $year = $request->input('year', null);
        $custom = $request->input('custom', null);
        $filterBy = $request->input('filter_by', null);


        $res2 = Task::where('department_id', $id)
            // ->whereDate('schedule', '>', Carbon::today())
            ->where('status', 3)
            ->where('d_status', 1)
            ->orderBy('updated_at', 'desc');


        $startDate = now()->startOfWeek()->toDateTimeString(); // Start of the current week
        $endDate = now()->endOfWeek()->toDateTimeString(); // End of the current week

        if ($filterBy == 'daily') {
            $res2 = $res2->whereDate('updated_at', Carbon::today())->orderBy('updated_at', 'desc')->get();
        }
        if ($filterBy == 'weekly') {
            $res2 = $res2->whereBetween('updated_at', [$startDate, $endDate])->orderBy('updated_at', 'desc')->get();
        }
        if ($filterBy == 'month' && $month && $year) {
            $res2 = $res2->whereMonth('updated_at', Carbon::parse($month))->whereYear('updated_at', $year)->orderBy('updated_at', 'desc')->get();
        }
        if ($filterBy == 'year' && $year) {
            $res2 = $res2->whereYear('updated_at', $year)->orderBy('updated_at', 'desc')->get();
        }
        if ($filterBy == 'custom' && $custom) {
            $res2 = $res2->whereDate('updated_at', Carbon::parse($custom)->format('Y-m-d'))->get();
        }

        // if ($today) {
        //     $tmp = $tmp->whereDate('updated_at', Carbon::today());
        // }

        // $res2 = $tmp->get()
        //     ->values();


        if (!$res2 || !$res2->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No accomplished tasks found for this department."
            ], 404);
        }

        // foreach ($res2 as $task) {
        //     $task->requestor;
        //     $task->assignee;
        //     // $task->issue;
        // }


        $groupedTasks = $res2->groupBy(function ($task) {
            return $task->updated_at->format('Y-m-d'); // Grouping tasks by date
        });


        $groupedTasks->transform(function ($tasks) {
            foreach ($tasks as $task) {
                // Load related models if needed
                $task->load('assignee', 'requestor');
            }
            return $tasks;
        });

        return [
            "data" => $groupedTasks,
            "success" => true,
        ];
    }

    public function top_employees(Request $request, $department_id)
    {

        $month = $request->input('month', null);
        $year = $request->input('year', null);
        $custom = $request->input('custom', null);
        $filterBy = $request->input('filter_by', null);

        $topAssignees = Task::select('assignee_id', DB::raw('COUNT(*) as completed_tasks'))
            ->with('assignee')
            ->where('status', 4);

        if ($department_id != 10000) $topAssignees = $topAssignees->where('department_id', $department_id);

        $startDate = now()->startOfWeek()->toDateTimeString(); // Start of the current week
        $endDate = now()->endOfWeek()->toDateTimeString(); // End of the current week

        if ($filterBy == 'daily') {
            $topAssignees = $topAssignees->whereDate('updated_at', Carbon::today());
        }
        if ($filterBy == 'weekly') {
            $topAssignees = $topAssignees->whereBetween('updated_at', [$startDate, $endDate]);
        }
        if ($filterBy == 'month' && $month && $year) {
            $topAssignees = $topAssignees->whereMonth('updated_at', $month + 1)->whereYear('updated_at', $year);
        }
        if ($filterBy == 'year' && $year) {
            $topAssignees = $topAssignees->whereYear('updated_at', $year);
        }

        $topAssignees = $topAssignees->groupBy('assignee_id')
            ->orderByDesc('completed_tasks')
            ->limit(50)
            ->get();

        return $topAssignees;
    }

    public function top_departments(Request $request)
    {

        $month = $request->input('month', null);
        $year = $request->input('year', null);
        $custom = $request->input('custom', null);
        $filterBy = $request->input('filter_by', null);

        $topAssignees = Task::select('department_id', DB::raw('COUNT(*) as completed_tasks'))
            ->with('department')
            ->where('status', 4);


        $startDate = now()->startOfWeek()->toDateTimeString(); // Start of the current week
        $endDate = now()->endOfWeek()->toDateTimeString(); // End of the current week

        if ($filterBy == 'daily') {
            $topAssignees = $topAssignees->whereDate('updated_at', Carbon::today());
        }
        if ($filterBy == 'weekly') {
            $topAssignees = $topAssignees->whereBetween('updated_at', [$startDate, $endDate]);
        }
        if ($filterBy == 'month' && $month && $year) {
            $topAssignees = $topAssignees->whereMonth('updated_at', $month + 1)->whereYear('updated_at', $year);
        }
        if ($filterBy == 'year' && $year) {
            $topAssignees = $topAssignees->whereYear('updated_at', $year);
        }

        $topAssignees = $topAssignees->groupBy('department_id')
            ->orderByDesc('completed_tasks')
            ->limit(50)
            ->get();

        return $topAssignees;
    }
}
