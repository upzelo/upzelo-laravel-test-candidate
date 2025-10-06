<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Http\Resources\TaskResource;
use App\Http\Resources\TaskCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Js;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks
     * @return JsonResponse
     * $todo Consider pagination of results and selecting specific fields to return as data set grows
     */
    public function index(): JsonResponse
    {
        $validated = request()->validate([
            'status' => 'nullable|in:pending,in_progress,completed',
        ]);
        if (isset($validated['status'])) {
            return new TaskCollection(
                Task::where('status', $validated['status'])
                    ->with('assignedUser')
                    ->get()
            )->response()->setStatusCode(200);
        } else {
            return new TaskCollection(
                Task::with('assignedUser')
                    ->get()
            )->response()->setStatusCode(200);
        }
    }

    /**
     * Store a newly created task
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'nullable|in:pending,in_progress,completed',
        ]);

        $task = Task::create($validated);

        return new TaskResource(Task::with('assignedUser')->find($task->id))->response()->setStatusCode(201);
    }

    /**
     * Display the specified task
     * @param Request $request
     * @param Task $task
     * @return JsonResponse
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $task->update($validated);
        $task->load('assignedUser');

        return (new TaskResource($task))->response()->setStatusCode(200);
    }
}
