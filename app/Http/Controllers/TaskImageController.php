<?php

namespace App\Http\Controllers;

use App\Models\Task_Image;
use App\Http\Requests\StoreTask_ImageRequest;
use App\Http\Requests\UpdateTask_ImageRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException as ExceptionFileNotFoundException;


#TODO: Task Images Controller

class TaskImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $path = 'task_images';

        if ($request->has('image')) {
            $file = $request->file('image');
            $task_id = $request->task_id;

            // $is_valid = $request->validate([
            //     'image' => 'mimes:jpeg,bmp,png|max:1128', // 1128 KB (1 MB) maximum size
            // ]);

            // Check if the file is an image based on its mime type
            if (!in_array($file->getClientMimeType(), ['image/jpeg', 'image/bmp', 'image/png', 'image/svg+xml'])) {
                return  response()->json(['error' => 'The uploaded file is not a valid image.']);
            }

            // Check the file size
            $maxFileSize = 1128 * 5000; // 1128 KB in bytes
            if ($file->getSize() > $maxFileSize) {
                return  response()->json(['error' => 'The image size exceeds the maximum allowed size of 5 MB.']);
            }

            $image = $request->file('image');
            $name = $request->name . '_' . time() . '.' . $image->getClientOriginalExtension();
            // $image->move('images/', $name);
            $filePath = $request->file('image')->storeAs($path, $name, 'uploads');
            // Storage::disk('myDisk')->put('/attribute_icons/' . $name, file_get_contents($image));

            $res = Task_Image::create(['task_id' => $task_id, 'url' => $filePath]);

            return response()->json(['success' => 'Uploaded successfully', 'path' => $path, 'name' => $name, 'image' => $res]);
        }
        return response()->json(['error' => 'Failed to upload.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task_Image $task_Image)
    {
        //
    }

    public function showImage($fileName)
    {
        $pathToFile = storage_path("app/uploads/task_images/" . $fileName);
        try {
            return response()->file($pathToFile);
        } catch (ExceptionFileNotFoundException $exception) {
            return response()->json("File not found.", 404);
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task_Image $task_Image)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTask_ImageRequest $request, Task_Image $task_Image)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task_Image $task_Image)
    {
        //
    }
}
