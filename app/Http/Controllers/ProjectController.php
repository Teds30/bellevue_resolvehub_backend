<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProjectController extends Controller
{


    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

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


    public function paginate(Request $request)
    {

        // return $request->input('page_size');
        // Retrieve page size and page number from the request, with default values if not provided
        $pageSize = $request->input('page_size', 10); // Default page size is 10
        $page = $request->input('page', 0); // Default page is 1
        $month = $request->input('month', null);
        $year = $request->input('year', null);
        $filterBy = $request->input('filter_by', null);
        $departmentId = $request->input('department_id', null);

        $tasks = Project::where('d_status', 1)->where('department_id', $departmentId)->with('department');

        if ($filterBy == 'today') {
            $tasks = $tasks->whereDate('created_at', Carbon::today());
        }
        if ($filterBy == 'this_week') {

            $startOfWeek = Carbon::now()->startOfWeek(); // Start of the week (Monday)
            $endOfWeek = Carbon::now()->endOfWeek(); // End of the week (Sunday)
            $tasks = $tasks->whereDate('created_at', [$startOfWeek, $endOfWeek]);
        }
        if ($filterBy == 'month' && $month && $year) {


            $tasks = $tasks->whereMonth('created_at', Carbon::parse($month))
                ->whereYear('created_at', $year);
        }
        if ($filterBy == 'year' && $year) {
            $tasks = $tasks->whereYear('created_at', $year);
        }

        $tasks = $tasks
            ->orderBy('created_at', 'desc')
            ->paginate($pageSize, ['*'], 'page', $page);


        // Check if any tasks are found
        if (!$tasks || !$tasks->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No projects found."
            ], 404);
        }


        // foreach ($tasks as $task) {
        //     $task['row_data'] = ['id' => $task->id, 'name' => $task->issue->name, 'created_at' => $task->created_at];
        // }


        // Return paginated tasks
        return response()->json([
            "data" => $tasks,
            "success" => true,
            "message" => "Projects fetched successfully."
        ], 200);
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


        if ($request->incharge_id != null && $request->requestor_id != null) {


            $incharge = User::get()->where('id', $request->incharge_id)->first();
            $requestor = User::get()->where('id', $request->requestor_id)->first();

            $targetDevices = $incharge->deviceTokens;

            // Decode the JSON string into a PHP array

            if ($targetDevices) {

                $data = json_decode($targetDevices, true);

                // Extract tokens using Laravel collection methods
                $tokens = collect($data)->pluck('token')->toArray();

                $project = $request->title;

                $args['title'] = "You have assigned as an In-charge to a project.";
                $args['body'] = "$requestor->first_name $requestor->last_name assigned you as the in-charge for the project [$project]";
                //  $args['targetDevice'] = $deviceTokenIOS;
                $args['targetDevices'] = $tokens;

                if ($tokens) {
                    $this->notificationService->sendPushNotification($args, true);
                }
            }
        }


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


        if ($request->incharge_id != null && $request->requestor_id != null) {


            $incharge = User::get()->where('id', $request->incharge_id)->first();
            $requestor = User::get()->where('id', $request->requestor_id)->first();

            $targetDevices = $incharge->deviceTokens;

            // Decode the JSON string into a PHP array

            if ($targetDevices) {

                $data = json_decode($targetDevices, true);

                // Extract tokens using Laravel collection methods
                $tokens = collect($data)->pluck('token')->toArray();

                $project = $res->title;

                $args['title'] = "You have assigned to a task.";
                $args['body'] = "$requestor->first_name $requestor->last_name assigned you as the in-charge for the project [$project]";
                // $args['targetDevice'] = $deviceTokenIOS;
                $args['targetDevices'] = $tokens;

                $sent = $this->notificationService->sendPushNotification($args, true);
            }
        }

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
