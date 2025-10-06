<?php

namespace App\Services;

use App\Models\Task;

class TaskService
{
    public function createTask(array $data): Task
    {
        return Task::create($data);
    }

    public function update(array $data, $id)
    {

        $task = Task::findOrFail($id);

        $task->update($data);

        return $task->fresh();
    }

    public function destroy($id)
    {

        $task = Task::findOrFail($id);
        return $task->delete();
    }

    public function getTasks(array $filters = [])
    {

        $tasks = Task::query();

        if (!empty($filters['status'])) {
            $tasks->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $tasks->where('priority', $filters['priority']);
        }

        return $tasks->get();
    }
}
