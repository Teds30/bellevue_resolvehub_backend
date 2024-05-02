<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Issue;
use App\Models\Notification;
use App\Models\Position;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
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
        $searchField = $request->input('searchField', null);
        $search = $request->input('search', null);


        // $tasks = Task::where('d_status', 1)->with('issue')->with('department')->with('assignee')->with('requestor')->with('assignor');
        $tasks = Task::where('d_status', 1)->where('department_id', $departmentId)->with('department')->with('assignee')->with('requestor')->with('assignor');

        if ($searchField && $search) {
            $tasks = $tasks->where($searchField, 'like', "%$search%");
        }
        if ($filterBy == 'daily') {
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
        if ($filterBy == 'custom' && $custom) {
            $tasks = $tasks->whereDate('created_at', Carbon::parse($custom)->format('Y-m-d'));
        }

        if ($status) {

            $_status = null;

            switch ($status) {
                case 'request':
                    $_status = 0;
                    break;
                case 'active':
                    $_status = 1;
                    break;
                case 'pending':
                    $_status = 2;
                    break;
                case 'cancelled':
                    $_status = 3;
                    break;
                case 'accomplished':
                    $_status = 4;
                    break;
            }
            $tasks = $tasks->where('status', $_status);
        }

        $tasks = $tasks
            ->orderBy('created_at', 'desc')
            ->paginate($pageSize, ['*'], 'page', $page);


        // Check if any tasks are found
        if (!$tasks || !$tasks->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "No tasks found."
            ], 404);
        }


        foreach ($tasks as $task) {
            //removed $task->issue->name;
            $task['row_data'] = ['id' => $task->id, 'name' => $task->issue, 'created_at' => $task->created_at];
        }


        // Return paginated tasks
        return response()->json([
            "data" => $tasks,
            "success" => true,
            "message" => "Tasks fetched successfully."
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
            'room' => 'required|string|max:255',
            'issue' => 'required|string|max:80',
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


        $positions = Position::get()->where('department_id', $request->department_id)->values();

        // Extract the position IDs
        $positionIds = $positions->pluck('id')->toArray();
        $users = User::whereHas('position', function ($query) use ($positionIds) {
            $query->whereIn('id', $positionIds);
        })->where('d_status', 1)->get();


        $sendTo = [];

        foreach ($users as $target) {
            $targetDevices = $target->deviceTokens;

            // Decode the JSON string into a PHP array
            $data = json_decode($targetDevices, true);

            // Extract tokens using Laravel collection methods
            $tokens = collect($data)->pluck('token')->toArray();

            $sendTo = array_merge($sendTo, $tokens);


            Notification::create(["title" => "New Issue Reported", "details" => "Equipment: Extra Towels", "receiver_id" => $target->id, "redirect_url" => "/"]);
        }


        // TODO: CHnage
        $issue = $res->issue;

        $args['title'] = "New Issue Reported";
        $args['body'] = $issue;
        // $args['targetDevice'] = $deviceTokenIOS;
        $args['targetDevices'] = $sendTo;

        //TODO: ENABLE FIREBASE
        if ($sendTo) {

            $this->notificationService->sendPushNotification($args, true);
        }

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


        // $res->issue;
        $res->department;
        $res->requestor->position->department;
        $res->assignee;
        $res->assignor;
        $res->pending_marker;
        $res->completed_marker;
        $res->task_images;

        if ($res->assignee) $res->assignee->position->department;
        if ($res->assignor) $res->assignor->position->department;
        if ($res->pending_marker) $res->pending_marker->position->department;
        if ($res->completed_marker) $res->completed_marker->position->department;


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

        // $res->issue;

        if (!$res || !$res->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "Task not found."
            ], 404);
        }

        $res->update($request->all());

        if ($request->assignee_id != null && $request->assignor_id != null) {


            $target = User::get()->where('id', $request->assignee_id)->first();
            $assignor = User::get()->where('id', $request->assignor_id)->first();


            $targetDevices = $target->deviceTokens;



            if ($targetDevices->count() > 0) {

                $data = json_decode($targetDevices, true);

                // Extract tokens using Laravel collection methods
                $tokens = collect($data)->pluck('token')->toArray();

                // $issue = $res->issue->name;
                $issue = $res->issue;
                $task_id = $res->id;

                $args['title'] = "New Task Assignment";
                $args['body'] = "$assignor->first_name $assignor->last_name assigned you a task [$issue]";
                $args['link'] = 'www.facebook.com';
                // $args['targetDevice'] = $deviceTokenIOS;
                $args['targetDevices'] = $tokens;


                if ($tokens) {

                    $this->notificationService->sendPushNotification($args, true);
                }
                Notification::create(["title" => "New Task Assignment", "details" => "$assignor->first_name $assignor->last_name assigned you a task [$issue]", "receiver_id" => $target->id, "redirect_url" => "/tasks/$task_id"]);
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


    public function issues_metric_week()
    {


        $startDate = now()->startOfWeek()->toDateTimeString(); // Start of the current week
        $endDate = now()->endOfWeek()->toDateTimeString(); // End of the current week

        $issuesBySchedule = Task::whereBetween('schedule', [$startDate, $endDate])
            ->select(DB::raw('DATE(schedule) as day'), DB::raw('count(*) as total'))
            ->groupBy(DB::raw('DATE(schedule)'))
            ->get();


        $formattedData = [];
        $daysOfWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        // Initialize formatted data with counts set to 0 for each day of the week
        foreach ($daysOfWeek as $day) {
            $formattedData[$day] = 0;
        }


        // return $issuesBySchedule[0];
        // return date('l', strtotime($issuesBySchedule[0]->schedule);
        // Update formatted data with actual counts from the retrieved data
        foreach ($issuesBySchedule as $issue) {
            $scheduleDay = date('D', strtotime($issue->day)); // Extract day name from datetime
            $formattedData[$scheduleDay] += $issue->total;
        }



        // Prepare the data in the format expected by the chart library
        $chartData = [
            'xAxis' => [['data' => $daysOfWeek]],
            'series' => [
                [
                    'data' => array_values($formattedData),
                    'area' => true,
                ],
            ],
        ];

        return $chartData;
    }

    public function issues_metric_month(Request $request)
    {

        $month = $request->input('month');
        $year = $request->input('year');
        // Calculate the start and end date for the current month
        // $startDate = now()->startOfMonth()->toDateString(); 
        // $endDate = now()->endOfMonth()->toDateString(); 
        $startDate = Carbon::createFromFormat('Y-m', $year . '-' . $month)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromFormat('Y-m', $year . '-' . $month)->endOfMonth()->toDateString();

        // Retrieve data for the current month
        $issuesBySchedule = Task::whereBetween('schedule', [$startDate, $endDate])
            ->select(DB::raw('DATE(schedule) as day'), DB::raw('count(*) as total'))
            ->groupBy(DB::raw('DATE(schedule)'))
            ->get();

        $formattedData = [];
        $daysOfMonth = [];

        // Generate an array of days in the current month
        $currentDate = now()->startOfMonth();
        while ($currentDate <= now()->endOfMonth()) {
            $daysOfMonth[] = $currentDate->format('j'); // Get day of the month without leading zeros
            $currentDate->addDay();
        }
        // Initialize formatted data with counts set to 0 for each day of the month
        foreach ($daysOfMonth as $day) {
            $formattedData[$day] = 0;
        }


        // Update formatted data with actual counts from the retrieved data
        foreach ($issuesBySchedule as $issue) {
            // Convert the day to an integer (assuming the day is already a numeric value)
            // Convert the date string to a Carbon instance
            $carbonDate = Carbon::createFromFormat('Y-m-d', $issue->day);

            // Extract the day of the month
            $dayOfMonth = $carbonDate->day;
            $formattedData[$dayOfMonth] = $issue->total;
        }


        // Prepare the data in the format expected by the chart library
        $chartData = [
            'xAxis' => [['data' => $daysOfMonth]],
            'series' => [
                [
                    'data' => array_values($formattedData),
                    'area' => true,
                ],
            ],
        ];

        return $chartData;
    }

    public function issues_metric_year(Request $request)
    {
        // $monthYear = $request->input('monthYear');
        $year = $request->input('year');


        $issuesByYear = Task::whereYear('created_at', $year)
            ->select(DB::raw('DATE(created_at) as month'), DB::raw('count(*) as total'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get();

        // return $issuesByYear;

        $monthsLabel = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $export = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

        $i = 1;

        // Initialize an array to hold total counts for each month
        $monthlyTotals = [
            "Jan" => 0,
            "Feb" => 0,
            "Mar" => 0,
            "Apr" => 0,
            "May" => 0,
            "Jun" => 0,
            "Jul" => 0,
            "Aug" => 0,
            "Sep" => 0,
            "Oct" => 0,
            "Nov" => 0,
            "Dec" => 0
        ];

        // Map the total counts to corresponding months
        foreach ($issuesByYear as $data) {
            $monthAbbreviation = date('M', strtotime($data['month']));
            $monthlyTotals[$monthAbbreviation] += $data['total'];
        }

        // Prepare the final data structure
        $result = [
            "month" => array_keys($monthlyTotals),
            "data" => array_values($monthlyTotals)
        ];


        // return ['month' => $monthsLabel, 'data' => $export];

        // foreach ($issuesByYear as $issue) {
        //     for($i; $i < 12; $i++) {
        //         if(Carbon::) {}
        //     }
        // }




        // Prepare the data in the format expected by the chart library
        $chartData = [
            'xAxis' => [["data" => array_keys($monthlyTotals)]],
            'series' => [
                [
                    'data' => array_values($monthlyTotals),
                    'area' => true,
                ],
            ],
        ];

        return $chartData;
    }


    public function issues_most_reported(Request $request)
    {


        $startDate = now()->startOfWeek()->toDateTimeString(); // Start of the current week
        $endDate = now()->endOfWeek()->toDateTimeString(); // End of the current week

        $mostReportedIssues = Task::whereBetween('schedule', [$startDate, $endDate])
            ->select('issue', DB::raw('count(*) as total'))
            ->groupBy('issue')
            ->orderByDesc('total')
            ->limit(50) // You can adjust this limit as needed
            ->get();

        foreach ($mostReportedIssues as $issue) {
            $issue->issue;
        }

        return $mostReportedIssues;
    }

    public function tasks_metric($department_id, $day)
    {

        $unassigned = null;
        $pending = null;
        $onGoing = null;
        $cancelled = null;
        $done = null;

        $startDate = now()->startOfWeek()->toDateTimeString(); // Start of the current week
        $endDate = now()->endOfWeek()->toDateTimeString(); // End of the current week


        $unassigned = Task::where('assignee_id', null)
            ->where('status', 0)
            ->where('department_id', $department_id);

        $pending = Task::where('department_id', $department_id)
            ->whereDate('schedule', '>', Carbon::today())
            ->where('completed_marker_id', null)
            ->where('d_status', 1);

        $onGoing = Task::whereDate('schedule', '<=', Carbon::today())
            ->where('department_id', $department_id)
            ->where('completed_marker_id', null)
            ->where('d_status', 1)
            ->where('status', '!=', 3);

        $cancelled = Task::where('status', 3)->where('department_id', $department_id);

        $done = Task::where('status', 4)
            ->where('completed_marker_id', '!=', null)
            ->where('d_status', 1)
            ->where('department_id', $department_id);

        // $total = Task::where('d_status', 1)->where('department_id', $department_id);
        // Task::whereBetween('schedule', [$startDate, $endDate])

        switch ($day) {
            case 'daily':
                $unassigned = $unassigned->whereDate('created_at', Carbon::now());
                $pending = $pending->whereDate('created_at', Carbon::now());
                $onGoing = $onGoing->whereDate('updated_at', Carbon::now());
                $cancelled = $cancelled->whereDate('updated_at', Carbon::now());
                $done = $done->whereDate('updated_at', Carbon::now());
                // ->count();
                break;
            case 'weekly':
                $unassigned = $unassigned->whereBetween('created_at', [$startDate, $endDate]);
                $pending = $pending->whereBetween('created_at', [$startDate, $endDate]);
                $onGoing = $onGoing->whereBetween('updated_at', [$startDate, $endDate]);
                $cancelled = $cancelled->whereBetween('updated_at', [$startDate, $endDate]);
                $done = $done->whereBetween('updated_at', [$startDate, $endDate]);
                // ->count();
                break;
            case 'monthly':
                $unassigned = $unassigned->whereMonth('created_at', Carbon::now()->month);
                $pending = $pending->whereMonth('created_at', Carbon::now()->month);
                $onGoing = $onGoing->whereMonth('updated_at', Carbon::now()->month);
                $cancelled = $cancelled->whereMonth('updated_at', Carbon::now()->month);
                $done = $done->whereMonth('updated_at', Carbon::now()->month);
                break;
            case 'yearly':
                $unassigned = $unassigned->whereYear('created_at', Carbon::now()->year);
                $pending = $pending->whereYear('created_at', Carbon::now()->year);
                $onGoing = $onGoing->whereYear('updated_at', Carbon::now()->year);
                $cancelled = $cancelled->whereYear('updated_at', Carbon::now()->year);
                $done = $done->whereYear('updated_at', Carbon::now()->year);
                break;
        }



        $totals = [
            "unassigned" => $unassigned->count(),
            "pending" => $pending->count(),
            "ongoing" => $onGoing->count(),
            "cancelled" => $cancelled->count(),
            "done" => $done->count(),
        ];

        $totals = array_sum($totals);

        return ["unassigned" => $unassigned->count(), "pending" => $pending->count(), "ongoing" => $onGoing->count(), "cancelled" => $cancelled->count(), "done" => $done->count(), "total" => $totals];
    }
}
