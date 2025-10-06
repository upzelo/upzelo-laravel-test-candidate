<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskCollection;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tasks = match (true) {
            $request->filled('overdue') => Task::overdue()->get(),
            $request->filled('status') => Task::where('status', $request->status)->get(),
            $request->filled('high_priority') => Task::where('priority', TaskPriority::High)->get(),
            default => Task::all(),
        };

        return new TaskCollection($tasks)->response()->setStatusCode(Response::HTTP_OK);
    }

    public function store(TaskRequest $request): JsonResponse
    {
        $data = $request->validated();
        $task = Task::create($data);

        return new TaskResource($task)->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $task = Task::findOrFail($id)->load('project');

        return new TaskResource($task)->response()->setStatusCode(Response::HTTP_OK);
    }

    public function update(TaskRequest $request, int $id): JsonResponse
    {
        $task = Task::findOrFail($id);
        $task->update($request->validated());

        return new TaskResource($task)->response()->setStatusCode(Response::HTTP_OK);
    }

    public function destroy(int $id): JsonResponse
    {
        $task = Task::findOrFail($id);
        $task->delete();
        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
