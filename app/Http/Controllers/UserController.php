<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
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

    public function user_data(Request $request)
    {
        // return $request;
        $user = $request->user();

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

        $department = $res->department;


        $res2 = Task::get()
            ->where('department_id', $department->id)
            ->where('schedule', null)
            ->where('d_status', 1)
            ->where('assignee_id', null)
            ->values();


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


    public function user_ongoing_tasks($user_id)
    {

        $res = User::get()->where('id', $user_id)->first();

        $department = $res->department;


        $res2 = Task::where('assignee_id', $user_id)
            ->whereDate('schedule', Carbon::today())
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

        return [
            "data" => $res2,
            "success" => true,
        ];
    }
}
