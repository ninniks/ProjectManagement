<?php

namespace App\Repositories;

use App\Exceptions\TaskNotFoundException;
use App\Interfaces\TaskRepositoryInterface;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;

class TaskRepository implements TaskRepositoryInterface
{

    public function all($project_id): Builder
    {
        return Task::query()
            ->whereRelation('project', 'id', '=', $project_id);
    }

    public function find($project_id, $task_id): Model|Builder
    {
        try {
            return Task::query()
                ->whereRelation('project', 'id', '=', $project_id)
                ->where('id', $task_id)
                ->firstOrFail();
        }catch (ModelNotFoundException){
            throw new TaskNotFoundException("Task with ID: $task_id not found", Response::HTTP_NOT_FOUND);
        }

    }

    public function create(array $data)
    {
        return Task::create($data)->load('assignee');
    }

    public function update(string $task_id, array $data): int
    {
        $task = Task::query()->where('id',$task_id)->first();
        //setting slug null for a moment to regenerate new slug on Update
        $task->slug = null;
        return $task->update($data);
    }
}
