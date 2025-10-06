<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApiResponseResource;
use App\Http\Resources\TaskCollection;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class TaskController
{
    public function __construct()
    {
    }

    public function index(Request $request, TaskService $taskService)
    {

        try {
            $filters = [
            'status' => $request->input('status'),
            'priority' => $request->input('priority'),
            ];

            $tasks = $taskService->getTasks($filters);

            return new TaskCollection($tasks)->response()->setStatusCode(200);
        } catch (Exception $e) {
            dd($e->getMessage());
            return new ApiResponseResource([
                'data' => [],
                'status' => 'error',
                'message' => 'Something went wrong! Please try again',

            ])->response()->setStatusCode(404);
        }
    }

    public function store(Request $request, TaskService $taskService)
    {
        try {
            $validated = $request->validate([
            'user_id'    => 'nullable|exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'status'     => 'in:pending,in_progress,completed',
            'priority'   => 'in:low,medium,high',
            ]);

            $task = $taskService->createTask($validated);

            return (new TaskResource($task))
                ->additional([
                    'status' => 'success',
                    'message' => 'Task created successfully'
                ])
                ->response()
                ->setStatusCode(201);
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $task = Task::findOrFail($id);

            return (new TaskResource($task))
                ->response()
                ->setStatusCode(200);
        } catch (ModelNotFoundException $e) {
            return (new ApiResponseResource([
               'data' => [],
               'status' => 'Error',
               'message' => 'Task Not Found'
            ]))
            ->response()
            ->setStatusCode(404);
        } catch (Exception $e) {
            return new ApiResponseResource([
               'data' => [],
               'status' => 'Error',
               'message' => 'Something Went wrong! Please try again',
            ])->response()->setStatusCode(500);
        }
    }

    public function update(Request $request, $id, TaskService $taskService)
    {
        try {
            $validated = $request->validate([
                'status' => 'sometimes|in:pending,in_progress,completed',
            ]);

            $task = $taskService->update($validated, $id);

            return new TaskResource($task)->additional([
                    'data' => [],
                    'status' => 'success',
                    'message' => 'Project created successfully'
                ])->response()
                ->setStatusCode(200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Task not found'], 404);
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}
