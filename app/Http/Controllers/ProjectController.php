<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Department;
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
        $custom = $request->input('custom', null);
        $filterBy = $request->input('filter_by', null);
        $departmentId = $request->input('department_id', null);
        $status = $request->input('status', null);
        $type = $request->input('type', null);
        $searchField = $request->input('searchField', null);
        $search = $request->input('search', null);
        $can_see_all = $request->input('can_see_all', null);

        $tasks = Project::where('d_status', 1)->with('department')->with('incharge');

        if ($can_see_all == false) {
            $tasks = $tasks->where('department_id', $departmentId);
        }
        if ($searchField && $search && $searchField != 'In-Charge') {
            $tasks = $tasks->where($searchField, 'like', "%$search%");
        }
        if ($searchField && $search && $searchField == 'In-Charge') {
            $searched_users = User::where('first_name', 'like', "%$search%")->orWhere('last_name', 'like', "%$search%")->pluck('id')
                ->toArray();
            $tasks = $tasks->whereIn('incharge_id', $searched_users);
        }

        if ($filterBy == 'daily') {
            $tasks = $tasks->whereDate('created_at', Carbon::now());
        }
        if ($filterBy == 'this_week') {

            $startOfWeek = Carbon::now()->startOfWeek(); // Start of the week (Monday)
            $endOfWeek = Carbon::now()->endOfWeek(); // End of the week (Sunday)
            $tasks = $tasks->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
        }
        if ($filterBy == 'month' && $month && $year) {


            $tasks = $tasks->whereMonth('created_at', Carbon::parse($month))
                ->whereYear('created_at', $year);
        }
        if ($filterBy == 'year' && $year) {
            $tasks = $tasks->whereYear('created_at', $year);
        }
        if ($filterBy == 'custom' && $custom) {
            $tasks = $tasks->whereDate('created_at', Carbon::parse($custom)->format('Y-m-d'));
        }

        // Check if any tasks are found
        if (!$tasks || !$tasks->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No projects found."
            ], 404);
        }


        if ($status) {

            switch ($status) {
                case 'request':
                    $tasks = $tasks->where('status', 0);

                    break;
                case 'pending':
                    $tasks = $tasks->where('status', 1);

                    break;
                case 'ongoing':
                    $tasks = $tasks->where('status', 2);

                    break;
                case 'cancelled':
                    $tasks = $tasks->where('status', 3);

                    break;
                case 'accomplished':
                    $tasks = $tasks->where('status', 4);

                    break;
                case 'rejected':
                    $tasks = $tasks->where('status', 5);

                    break;
            }
            // $tasks = $tasks->where('status', $_status);
        }
        if ($type) {

            switch ($type) {
                case 'minor':
                    $tasks = $tasks->where('type', 0);

                    break;
                case 'major':
                    $tasks = $tasks->where('type', 1);

                    break;
            }
            // $tasks = $tasks->where('status', $_status);
        }
        // foreach ($tasks as $task) {
        //     $task['row_data'] = ['id' => $task->id, 'name' => $task->issue->name, 'created_at' => $task->created_at];
        // }

        $tasks = $tasks
            ->orderBy('created_at', 'desc')
            ->paginate($pageSize, ['*'], 'page', $page);




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


    public function department_projects(Request $request, $id, $day)
    {
        $pageSize = $request->input('page_size', 10); // Default page size is 10
        $page = $request->input('page', 1);
        $status = intVal($request->input('status', null));
        $month = $request->input('month', null);
        $year = $request->input('year', null);
        $custom = $request->input('custom', null);
        $filterBy = $request->input('filter_by', null);

        $res = Project::where('department_id', $id);

        $startDate = now()->startOfWeek()->toDateTimeString(); // Start of the current week
        $endDate = now()->endOfWeek()->toDateTimeString(); // End of the current week

        $project = $res->where('status', $status)
            ->where('d_status', 1);
        // return ['ads' => $project->get()];



        switch ($day) {
            case 'daily':
                if ($status == 0 || $status == 1) {
                    $project = $project->whereDate('created_at', Carbon::now())
                        ->orderBy('created_at', 'desc');
                } else {

                    $project = $project->whereDate('updated_at', Carbon::now())
                        ->orderBy('updated_at', 'desc');
                }
                break;
            case 'weekly':
                if ($status == 0 || $status == 1) {
                    $project = $project->whereBetween('created_at', [$startDate, $endDate])
                        ->orderBy('created_at', 'desc');
                } else {
                    $project = $project->whereBetween('updated_at', [$startDate, $endDate])
                        ->orderBy('updated_at', 'desc');
                }
                break;
            case 'monthly':
                // $monthNumeric = Carbon::parse($month)->month + 1;

                if ($status == 0 || $status == 1) {
                    $project = $project->whereMonth('created_at', Carbon::parse($month))
                        ->orderBy('created_at', 'desc');
                } else {
                    $project = $project->whereMonth('updated_at', Carbon::parse($month))
                        ->orderBy('updated_at', 'desc');
                }
                break;
            case 'yearly':
                if ($status == 0 || $status == 1) {
                    $project = $project->whereYear('created_at', $year)
                        ->orderBy('created_at', 'desc');
                } else {
                    $project = $project->whereYear('updated_at', $year)
                        ->orderBy('updated_at', 'desc');
                }
                break;
        }


        $project = $project
            ->paginate($pageSize, ['*'], 'page', $page);

        if (!$project || !$project->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No projects yet."
            ], 404);
        }

        return [
            "data" => $project,
            "success" => true,
        ];
    }

    public function user_projects(Request $request, $id, $day)
    {

        $pageSize = $request->input('page_size', 10); // Default page size is 10
        $page = $request->input('page', 1);
        $status = intVal($request->input('status', null));
        $month = $request->input('month', null);
        $year = $request->input('year', null);
        $custom = $request->input('custom', null);
        $filterBy = $request->input('filter_by', null);

        $res = Project::where('requestor_id', $id)->where('incharge_id', '!=', $id);

        $startDate = now()->startOfWeek()->toDateTimeString(); // Start of the current week
        $endDate = now()->endOfWeek()->toDateTimeString(); // End of the current week

        $project = $res->where('status', $status)
            ->where('d_status', 1);



        switch ($day) {
            case 'daily':
                if ($status == 0 || $status == 1) {
                    $project = $project->whereDate('created_at', Carbon::now())
                        ->orderBy('created_at', 'desc');
                } else {

                    $project = $project->whereDate('updated_at', Carbon::now())
                        ->orderBy('updated_at', 'desc');
                }
                break;
            case 'weekly':
                if ($status == 0 || $status == 1) {
                    $project = $project->whereBetween('created_at', [$startDate, $endDate])
                        ->orderBy('created_at', 'desc');
                } else {
                    $project = $project->whereBetween('updated_at', [$startDate, $endDate])
                        ->orderBy('updated_at', 'desc');
                }
                break;
            case 'monthly':
                // $monthNumeric = Carbon::parse($month)->month + 1;

                if ($status == 0 || $status == 1) {
                    $project = $project->whereMonth('created_at', Carbon::parse($month))
                        ->orderBy('created_at', 'desc');
                } else {
                    $project = $project->whereMonth('updated_at', Carbon::parse($month))
                        ->orderBy('updated_at', 'desc');
                }
                break;
            case 'yearly':
                if ($status == 0 || $status == 1) {
                    $project = $project->whereYear('created_at', $year)
                        ->orderBy('created_at', 'desc');
                } else {
                    $project = $project->whereYear('updated_at', $year)
                        ->orderBy('updated_at', 'desc');
                }
                break;
        }

        $project = $project
            ->paginate($pageSize, ['*'], 'page', $page);

        if (!$project || !$project->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No projects yet."
            ], 404);
        }

        return [
            "data" => $project,
            "success" => true,
        ];
    }
    public function assigned_projects(Request $request, $id, $day)
    {
        $pageSize = $request->input('page_size', 10); // Default page size is 10
        $page = $request->input('page', 1);
        $status = intVal($request->input('status', null));
        $month = $request->input('month', null);
        $year = $request->input('year', null);
        $custom = $request->input('custom', null);
        $filterBy = $request->input('filter_by', null);

        $res = Project::where('incharge_id', $id);

        $startDate = now()->startOfWeek()->toDateTimeString(); // Start of the current week
        $endDate = now()->endOfWeek()->toDateTimeString(); // End of the current week

        $project = $res->where('status', $status)
            ->where('d_status', 1);



        switch ($day) {
            case 'daily':
                if ($status == 0 || $status == 1) {
                    $project = $project->whereDate('created_at', Carbon::now())
                        ->orderBy('created_at', 'desc');
                } else {

                    $project = $project->whereDate('updated_at', Carbon::now())
                        ->orderBy('updated_at', 'desc');
                }
                break;
            case 'weekly':
                if ($status == 0 || $status == 1) {
                    $project = $project->whereBetween('created_at', [$startDate, $endDate])
                        ->orderBy('created_at', 'desc');
                } else {
                    $project = $project->whereBetween('updated_at', [$startDate, $endDate])
                        ->orderBy('updated_at', 'desc');
                }
                break;
            case 'monthly':
                // $monthNumeric = Carbon::parse($month)->month + 1;

                if ($status == 0 || $status == 1) {
                    $project = $project->whereMonth('created_at', Carbon::parse($month))
                        ->orderBy('created_at', 'desc');
                } else {
                    $project = $project->whereMonth('updated_at', Carbon::parse($month))
                        ->orderBy('updated_at', 'desc');
                }
                break;
            case 'yearly':
                if ($status == 0 || $status == 1) {
                    $project = $project->whereYear('created_at', $year)
                        ->orderBy('created_at', 'desc');
                } else {
                    $project = $project->whereYear('updated_at', $year)
                        ->orderBy('updated_at', 'desc');
                }
                break;
        }

        $project = $project
            ->paginate($pageSize, ['*'], 'page', $page);

        if (!$project || !$project->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No projects yet."
            ], 404);
        }

        return [
            "data" => $project,
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

                if ($tokens) {

                    $sent = $this->notificationService->sendPushNotification($args, true);
                }
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

    public function projects_metric(Request $request, $department_id, $day)
    {

        $month = $request->input('month');
        $year = $request->input('year');

        $request = null;
        $pending = null;
        $onGoing = null;
        $cancelled = null;
        $done = null;
        $rejected = null;

        $startDate = now()->startOfWeek()->toDateTimeString(); // Start of the current week
        $endDate = now()->endOfWeek()->toDateTimeString(); // End of the current week


        $project = Project::where('department_id', $department_id);


        $requested = Project::where('status', 0)
            ->where('d_status', 1);
        if ($department_id != 10000) $requested = $requested->where('department_id', $department_id);

        $pending = Project::where('status', 1)
            ->where('d_status', 1);
        if ($department_id != 10000) $pending = $pending->where('department_id', $department_id);

        $onGoing = Project::where('status', 2)
            ->where('d_status', 1);
        if ($department_id != 10000) $onGoing = $onGoing->where('department_id', $department_id);

        $cancelled = Project::where('status', 3)
            ->where('d_status', 1);
        if ($department_id != 10000) $cancelled = $cancelled->where('department_id', $department_id);

        $done = Project::where('status', 4)
            ->where('d_status', 1);
        if ($department_id != 10000) $done = $done->where('department_id', $department_id);

        $rejected = Project::where('status', 5)
            ->where('d_status', 1);
        if ($department_id != 10000) $rejected = $rejected->where('department_id', $department_id);


        // return ['ps' => $requested->get()];

        switch ($day) {
            case 'daily':

                $requested = $requested->whereDate('created_at', Carbon::now());
                $pending = $pending->whereDate('created_at', Carbon::now());
                $onGoing = $onGoing->whereDate('updated_at', Carbon::now());
                $cancelled = $cancelled->whereDate('updated_at', Carbon::now());
                $done = $done->whereDate('updated_at', Carbon::now());
                $rejected = $rejected->whereDate('updated_at', Carbon::now());
                // ->count();
                break;
            case 'weekly':
                $requested = $requested->whereBetween('created_at', [$startDate, $endDate]);
                $pending = $pending->whereBetween('created_at', [$startDate, $endDate]);
                $onGoing = $onGoing->whereBetween('updated_at', [$startDate, $endDate]);
                $cancelled = $cancelled->whereBetween('updated_at', [$startDate, $endDate]);
                $done = $done->whereBetween('updated_at', [$startDate, $endDate]);
                $rejected = $rejected->whereBetween('updated_at', [$startDate, $endDate]);
                // ->count();
                break;
            case 'monthly':
                $requested = $requested->whereMonth('created_at', $month);
                $pending = $pending->whereMonth('created_at', $month);
                $onGoing = $onGoing->whereMonth('updated_at', $month);
                $cancelled = $cancelled->whereMonth('updated_at', $month);
                $done = $done->whereMonth('updated_at', $month);
                $rejected = $rejected->whereMonth('updated_at', $month);
                break;
            case 'yearly':
                $requested = $requested->whereYear('created_at', $year);
                $pending = $pending->whereYear('created_at', $year);
                $onGoing = $onGoing->whereYear('updated_at', $year);
                $cancelled = $cancelled->whereYear('updated_at', $year);
                $done = $done->whereYear('updated_at', $year);
                $rejected = $rejected->whereYear('updated_at', $year);
                break;
        }



        $totals = [
            "requested" => $requested->count(),
            "pending" => $pending->count(),
            "ongoing" => $onGoing->count(),
            "cancelled" => $cancelled->count(),
            "done" => $done->count(),
            "rejected" => $rejected->count(),
        ];

        $totals = array_sum($totals);

        return ["requested" => $requested->count(), "pending" => $pending->count(), "ongoing" => $onGoing->count(), "cancelled" => $cancelled->count(), "done" => $done->count(), "rejected" => $rejected->count(), "total" => $totals];
    }


    public function projects_metric_distribution(Request $request, $day)
    {

        $month = $request->input('month');
        $year = $request->input('year');

        $requested = null;
        $pending = null;
        $onGoing = null;
        $cancelled = null;
        $done = null;

        $startDate = now()->startOfWeek()->toDateTimeString(); // Start of the current week
        $endDate = now()->endOfWeek()->toDateTimeString(); // End of the current week

        $departments_list = Department::where('d_status', 1)->get();

        $out = [];

        foreach ($departments_list as $department) {
            $department_id = $department->id;

            $requested = Project::where('status', 0)
                ->where('d_status', 1);

            if ($department_id != 10000) $requested = $requested->where('department_id', $department_id);

            $pending = Project::where('status', 1)
                ->where('d_status', 1);

            if ($department_id != 10000) $pending = $pending->where('department_id', $department_id);


            $onGoing = Project::where('status', 2)
                ->where('d_status', 1);
            if ($department_id != 10000) $onGoing = $onGoing->where('department_id', $department_id);


            $cancelled = Project::where('status', 3)
                ->where('d_status', 1);
            if ($department_id != 10000) $cancelled = $cancelled->where('department_id', $department_id);

            $done = Project::where('status', 4)
                ->where('d_status', 1);
            if ($department_id != 10000) $done = $done->where('department_id', $department_id);

            $rejected = Project::where('status', 5)
                ->where('d_status', 1);
            if ($department_id != 10000) $rejected = $rejected->where('department_id', $department_id);

            // $total = Task::where('d_status', 1)->where('department_id', $department_id);
            // Task::whereBetween('schedule', [$startDate, $endDate])

            switch ($day) {
                case 'daily':
                    $requested = $requested->whereDate('created_at', Carbon::now());
                    $pending = $pending->whereDate('created_at', Carbon::now());
                    $onGoing = $onGoing->whereDate('updated_at', Carbon::now());
                    $cancelled = $cancelled->whereDate('updated_at', Carbon::now());
                    $done = $done->whereDate('updated_at', Carbon::now());
                    $rejected = $rejected->whereDate('updated_at', Carbon::now());
                    // ->count();
                    break;
                case 'weekly':
                    $requested = $requested->whereBetween('created_at', [$startDate, $endDate]);
                    $pending = $pending->whereBetween('created_at', [$startDate, $endDate]);
                    $onGoing = $onGoing->whereBetween('updated_at', [$startDate, $endDate]);
                    $cancelled = $cancelled->whereBetween('updated_at', [$startDate, $endDate]);
                    $done = $done->whereBetween('updated_at', [$startDate, $endDate]);
                    $rejected = $rejected->whereBetween('updated_at', [$startDate, $endDate]);
                    // ->count();
                    break;
                case 'monthly':
                    $requested = $requested->whereMonth('created_at', $month);
                    $pending = $pending->whereMonth('created_at', $month);
                    $onGoing = $onGoing->whereMonth('updated_at', $month);
                    $cancelled = $cancelled->whereMonth('updated_at', $month);
                    $done = $done->whereMonth('updated_at', $month);
                    $rejected = $rejected->whereMonth('updated_at', $month);
                    break;
                case 'yearly':
                    $requested = $requested->whereYear('created_at', $year);
                    $pending = $pending->whereYear('created_at', $year);
                    $onGoing = $onGoing->whereYear('updated_at', $year);
                    $cancelled = $cancelled->whereYear('updated_at', $year);
                    $done = $done->whereYear('updated_at', $year);
                    $rejected = $rejected->whereYear('updated_at', $year);
                    break;
            }



            $totals = [
                "requested" => $requested->count(),
                "pending" => $pending->count(),
                "ongoing" => $onGoing->count(),
                "cancelled" => $cancelled->count(),
                "done" => $done->count(),
                "rejected" => $rejected->count(),
            ];

            $totals = array_sum($totals);

            $output[] = ["department" => $department, "requested" => $requested->count(), "pending" => $pending->count(), "ongoing" => $onGoing->count(), "cancelled" => $cancelled->count(), "done" => $done->count(), "rejected" => $rejected->count(), "total" => $totals];
        }

        usort($output, function ($a, $b) {
            return $b['total'] - $a['total'];
        });

        return $output;
    }
}
