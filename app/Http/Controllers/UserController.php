<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
            'position_id' => 'required|integer',
            'phone_number' => 'nullable|string',
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

    public function user_data(Request $request)
    {
        // return $request;
        $user = $request->user();
        $user->position->department;

        $permissions = [];

        foreach ($user->position->permissions as $perm) {
            $permissions[] = $perm->access_code;
        }

        unset($user->position->permissions);
        $user['permissions'] = $permissions;

        if (!$user) {
            return response()->json(['data' => [], 'success' => false, 'message' => 'Unauthorized'], 401);
        }

        return response()->json(['data' => $user, 'success' => true, 'message' => null], 200);
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

        if ($request->password) {

            $res->update(['password' => Hash::make($request->password)]);
        }

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


    public function user_assigned_tasks($user_id)
    {

        $res = User::get()->where('id', $user_id)->first();

        $department = $res->position->department;



        $res2 = Task::where('department_id', $department->id)
            ->where('schedule', null)
            ->where('assignee_id', null)
            ->where('completed_marker_id', null)
            ->orderBy('created_at', 'desc')
            ->get();



        foreach ($res2 as $task) {
            // $task->issue;
            $task->assignee;
            $task->requestor;
        }


        if (!$res2 || !$res2->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No assigned tasks found."
            ], 404);
        }

        return [
            "data" => $res2,
            "success" => true,
        ];
    }

    public function user_raised_issues(Request $request, $user_id)
    {

        $month = $request->input('month', null);
        $year = $request->input('year', null);
        $custom = $request->input('custom', null);
        $filterBy = $request->input('filter_by', null);

        $res = User::get()->where('id', $user_id)->first();

        $department = $res->position->department;


        $startDate = now()->startOfWeek()->toDateTimeString(); // Start of the current week
        $endDate = now()->endOfWeek()->toDateTimeString(); // End of the current week

        $res2 = Task::where('department_id', '!=', $department->id)
            ->where('d_status', 1)
            ->where(function ($query) use ($user_id) {
                $query->where('requestor_id', $user_id);
            });



        if (!$res2 || !$res2->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No assigned tasks found."
            ], 404);
        }


        if ($filterBy == 'daily') {
            $res2 = $res2->whereDate('created_at', Carbon::today())->orderBy('created_at', 'desc')->get();
        }
        if ($filterBy == 'weekly') {
            $res2 = $res2->whereBetween('created_at', [$startDate, $endDate])->orderBy('created_at', 'desc')->get();
        }
        if ($filterBy == 'month' && $month && $year) {
            $res2 = $res2->whereMonth('created_at', Carbon::parse($month))->whereYear('created_at', $year)->orderBy('created_at', 'desc')->get();
        }
        if ($filterBy == 'year' && $year) {
            $res2 = $res2->whereYear('created_at', $year)->orderBy('created_at', 'desc')->get();
        }
        if ($filterBy == 'custom' && $custom) {
            $res2 = $res2->whereDate('created_at', Carbon::parse($custom)->format('Y-m-d'))->get();
        }


        $groupedTasks = $res2->groupBy(function ($task) {
            return $task->created_at->format('Y-m-d'); // Grouping tasks by date
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


    public function user_ongoing_tasks($user_id)
    {

        $res = User::get()->where('id', $user_id)->first();

        $department = $res->department;


        $res2 = Task::where('assignee_id', $user_id)
            ->whereDate('schedule', '<=', Carbon::today())
            ->where('completed_marker_id', null)
            ->where('d_status', 1)
            ->get()
            ->values();


        if (!$res2 || !$res2->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No On-Going tasks found."
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


    public function user_pending_tasks($user_id)
    {

        $res = User::get()->where('id', $user_id)->first();

        $department = $res->department;

        $res2 = Task::where('assignee_id', $user_id)
            ->whereDate('schedule', '>', Carbon::today())
            ->where('completed_marker_id', null)
            ->where('d_status', 1)
            ->get()
            ->values();



        if (!$res2 || !$res2->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No pending tasks found."
            ], 404);
        }


        foreach ($res2 as $task) {
            // $task->issue;
            $task->assignee;
            $task->requestor;
        }

        return [
            "data" => $res2,
            "success" => true,
        ];
    }

    public function user_assigned_projects($user_id)
    {


        $res2 = Project::get()->where('incharge_id', $user_id)->first();


        if (!$res2 || !$res2->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No projects found."
            ]);
        }

        $projects = $res2->projects;

        return [
            "data" => $projects,
            "success" => true,
            "message" => null
        ];
    }
    public function user_done_tasks(Request $request, $id)
    {


        $month = $request->input('month', null);
        $year = $request->input('year', null);
        $custom = $request->input('custom', null);
        $filterBy = $request->input('filter_by', null);

        $res2 = Task::where('assignee_id', $id)
            // ->whereDate('schedule', '>', Carbon::today())
            ->where('completed_marker_id', '!=', null)
            ->where('d_status', 1);


        // if ($today) {

        //     $tmp = $tmp->whereDate('updated_at', Carbon::today());
        // }

        // $res2 = $tmp->get()
        //     ->values();


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


        if (!$res2 || !$res2->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No accomplished tasks found."
            ], 404);
        }


        foreach ($res2 as $task) {
            $task->issue;
            $task->assignee;
            $task->requestor;
        }

        return [
            "data" => $res2,
            "success" => true,
        ];
    }
    public function user_cancelled_tasks(Request $request, $id)
    {

        $today = $request->today ?? false;
        $tmp = Task::where('assignee_id', $id)
            ->where('status', 3)
            ->where('d_status', 1)
            ->orderBy('updated_at', 'desc');


        if ($today) {

            $tmp = $tmp->whereDate('updated_at', Carbon::today());
        }

        $res2 = $tmp->get()
            ->values();



        if (!$res2 || !$res2->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No accomplished tasks found."
            ], 404);
        }


        foreach ($res2 as $task) {
            $task->issue;
            $task->assignee;
            $task->requestor;
        }

        return [
            "data" => $res2,
            "success" => true,
        ];
    }
}
