<?php

use App\Exports\TasksExport;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DeviceTokenController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProjectCommentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskAccomplishImageController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskImageController;
use App\Http\Controllers\UserController;
use App\Models\Department;
use App\Models\DeviceToken;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\Task;
use App\Models\Task_Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('positions', [PositionController::class, "index"]);
Route::get('positions/{id}', [PositionController::class, "show"]);
Route::post('positions', [PositionController::class, "store"]);
Route::patch('positions/{id}', [PositionController::class, "update"]);
Route::delete('positions/{id}', [PositionController::class, "destroy"]);
Route::get('position_permissions/{id}', [PositionController::class, "permissions"]);
Route::patch('update_permissions/{id}', [PositionController::class, "update_permissions"]);


Route::get('departments', [DepartmentController::class, "index"]);
Route::get('departments/{id}', [DepartmentController::class, "show"]);
Route::post('departments', [DepartmentController::class, "store"]);
Route::patch('departments/{id}', [DepartmentController::class, "update"]);
Route::delete('departments/{id}', [DepartmentController::class, "destroy"]);
Route::get('department_employees/{id}', [DepartmentController::class, "department_employees"]);
Route::get('department_positions/{id}', [DepartmentController::class, "department_positions"]);


Route::get('issues', [IssueController::class, "index"]);
Route::get('issues/{id}', [IssueController::class, "show"]);
Route::post('issues', [IssueController::class, "store"]);
Route::patch('issues/{id}', [IssueController::class, "update"]);
Route::delete('issues/{id}', [IssueController::class, "destroy"]);

Route::get('issues_recommendation', [TaskController::class, "issues_recommendation"]);

// Route::get('task_images', [IssueController::class, "index"]);
// Route::get('task_images/{id}', [IssueController::class, "show"]);
Route::post('task_images', [TaskImageController::class, "store"]);
Route::post('task_accomplish_images', [TaskAccomplishImageController::class, "store"]);
Route::get('task_images/{fileName}', [TaskImageController::class, "showImage"]);
Route::get('task_accomplish_images/{fileName}', [TaskAccomplishImageController::class, "showImage"]);
// Route::patch('task_images/{id}', [IssueController::class, "update"]);
// Route::delete('task_images/{id}', [IssueController::class, "destroy"]);

Route::get('users/{id}', [UserController::class, "show"]);
Route::post('users', [UserController::class, "store"]);
Route::patch('users/{id}', [UserController::class, "update"]);
Route::delete('users/{id}', [UserController::class, "destroy"]);
Route::get('user_assigned_tasks/{id}', [UserController::class, "user_assigned_tasks"]);
Route::get('user_ongoing_tasks/{id}', [UserController::class, "user_ongoing_tasks"]);
Route::get('user_done_tasks/{id}', [UserController::class, "user_done_tasks"]);
Route::get('user_cancelled_tasks/{id}', [UserController::class, "user_cancelled_tasks"]);
Route::get('user_pending_tasks/{id}', [UserController::class, "user_pending_tasks"]);
Route::get('user_assigned_projects/{id}', [UserController::class, "user_assigned_projects"]);
Route::get('user_raised_issues/{id}', [UserController::class, "user_raised_issues"]);
Route::post('projects_page', [ProjectController::class, "paginate"]);


Route::get('tasks', [TaskController::class, "index"]);
Route::post('tasks_page', [TaskController::class, "paginate"]);
Route::get('tasks/{id}', [TaskController::class, "show"]);
Route::post('tasks', [TaskController::class, "store"]);
Route::patch('tasks/{id}', [TaskController::class, "update"]);
Route::delete('tasks/{id}', [TaskController::class, "destroy"]);

Route::get('projects', [ProjectController::class, "index"]);
Route::get('projects/{id}', [ProjectController::class, "show"]);
Route::post('projects', [ProjectController::class, "store"]);
Route::patch('projects/{id}', [ProjectController::class, "update"]);
Route::delete('projects/{id}', [ProjectController::class, "destroy"]);
Route::get('department_projects/{id}/{day}', [ProjectController::class, "department_projects"]);
Route::get('assigned_to_projects/{id}/{day}', [ProjectController::class, "user_projects"]);
Route::get('my_projects/{id}/{day}', [ProjectController::class, "assigned_projects"]);
Route::get('project-comments/{project_id}', [ProjectCommentController::class, "comments"]);
Route::post('project-comments', [ProjectCommentController::class, "store"]);

Route::get('notifications', [NotificationController::class, "index"]);
Route::get('notifications/{id}', [NotificationController::class, "show"]);
Route::get('user_notifications/{id}', [NotificationController::class, "user_notifications"]);
Route::post('notifications', [NotificationController::class, "store"]);
Route::patch('notifications/{id}', [NotificationController::class, "update"]);
Route::delete('notifications/{id}', [NotificationController::class, "destroy"]);

Route::post('device_tokens', [DeviceTokenController::class, "store"]);
Route::delete('device_tokens', [DeviceTokenController::class, "destroy"]);

Route::get('issues_metric/week/{department_id}', [TaskController::class, "issues_metric_week"]);
Route::post('issues_metric/month/{department_id}', [TaskController::class, "issues_metric_month"]);
Route::post('issues_metric/year/{department_id}', [TaskController::class, "issues_metric_year"]);


Route::get('issues_most_reported/daily/{department_id}', [TaskController::class, "issues_most_reported_daily"]);
Route::get('issues_most_reported/week/{department_id}', [TaskController::class, "issues_most_reported_weekly"]);
Route::post('issues_most_reported/month/{department_id}', [TaskController::class, "issues_most_reported_monthly"]);
Route::post('issues_most_reported/year/{department_id}', [TaskController::class, "issues_most_reported_yearly"]);
Route::post('tasks_metric/{department_id}/{day}', [TaskController::class, "tasks_metric"]);

Route::post('tasks_metric_distribution/{day}', [TaskController::class, "tasks_metric_distribution"]);
Route::post('projects_metric_distribution/{day}', [ProjectController::class, "projects_metric_distribution"]);


Route::post('projects_metric/{department_id}/{day}', [ProjectController::class, "projects_metric"]);

//TODO: Permissions

Route::post('login', [AuthController::class, "login"]);
Route::post('register', [AuthController::class, "register"]);
Route::post('forgot_password', [AuthController::class, "updatePassword"]);

Route::middleware('auth:api')->group(function () {
    Route::get('user_data', [UserController::class, "user_data"]);
});

//TODO: Move these to auth middleware

Route::get('department_assigned_tasks/{id}', [DepartmentController::class, "department_assigned_tasks"]);
Route::get('department_ongoing_tasks/{id}', [DepartmentController::class, "department_ongoing_tasks"]);
Route::get('department_pending_tasks/{id}', [DepartmentController::class, "department_pending_tasks"]);
Route::get('department_done_tasks/{id}', [DepartmentController::class, "department_done_tasks"]);
Route::get('department_cancelled_tasks/{id}', [DepartmentController::class, "department_cancelled_tasks"]);

Route::get('top_employees/{department_id}', [DepartmentController::class, "top_employees"]);
Route::get('top_departments', [DepartmentController::class, "top_departments"]);


Route::post('tasks_export', [TaskController::class, "export"]);

// Route::post('register', [AuthController::class, "register"]);
