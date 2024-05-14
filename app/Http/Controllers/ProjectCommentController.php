<?php

namespace App\Http\Controllers;

use App\Models\ProjectComment;
use App\Http\Requests\StoreProjectCommentRequest;
use App\Http\Requests\UpdateProjectCommentRequest;
use Illuminate\Http\Request;

class ProjectCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function comments($project_id)
    {
        $res = ProjectComment::where('project_id', $project_id)->orderBy('created_at', 'desc')->get();

        if (!$res || !$res->count()) {
            return response()->json([
                "data" => [],
                "success" => false,
                "message" => "Project not found."
            ], 404);
        }

        foreach ($res as $proj) {
            $proj->project;
            $proj->commentor->position;
        }

        $total = $res->count();

        return [
            "data" => [
                "comments" => $res,
                "total" => $total
            ],
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
            'commentor_id' => 'required|integer',
            'project_id' => 'required|integer',
            'comment' => 'required|string',
        ]);


        $res2 = ProjectComment::create($request->all());
        return response()->json($res2, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProjectComment $projectComment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProjectComment $projectComment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectCommentRequest $request, ProjectComment $projectComment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $res = ProjectComment::get()->where('id', $id)->first();

        if (!$res || !$res->count()) {
            return response()->json([
                "success" => false,
                "message" => "Token not found."
            ], 404);
        }

        $res->delete();

        return [
            "success" => true,
            "message" => "Successfully deleted."
        ];
    }
}
