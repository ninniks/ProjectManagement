<?php

namespace App\Http\Controllers;

use App\Enum\ProjectStatusEnum;
use App\Exceptions\ProjectNotFoundException;
use App\Exceptions\TaskNotFoundException;
use App\Http\Rules\FilterRequest;
use App\Http\Rules\PatchTaskFormRequest;
use App\Http\Rules\StoreTaskFormRequest;
use App\Http\Traits\QueryUtilityTrait;
use App\Interfaces\TaskRepositoryInterface;
use App\Repositories\ProjectRepository;
use App\Repositories\TaskRepository;
use Illuminate\Http\JsonResponse;

class ProjectTaskController extends Controller
{
    use QueryUtilityTrait;

    private ProjectRepository $projectRepository;
    private TaskRepositoryInterface $taskRepository;

    public function __construct()
    {
        $this->taskRepository = new TaskRepository();
        $this->projectRepository = new ProjectRepository();
    }

    public function index(FilterRequest $request, $project_id): JsonResponse
    {
        $sortBy = $request->input('sortBy');
        $perPage = $request->input('perPage');
        $page = $request->input('page');

        $tasks = $this->taskRepository->all($project_id);

        $tasks = $this->sort($tasks, $sortBy)
            ->paginate(
                $perPage,
                ['*'],
                'page',
                $page);

        return response()->json($tasks);
    }

    public function show($project_id, $task_id): JsonResponse
    {
        try {
            $task = $this->taskRepository->find($project_id, $task_id);
        }catch (TaskNotFoundException $e){
            return response()->json(["error" => $e->getMessage()], 404);
        }

        return response()->json(["data" => $task]);
    }

    public function store(StoreTaskFormRequest $request, $project_id): JsonResponse
    {
        $new_task = $request->validated();
        if($request->has('assignee')){
            $new_task['assignee_id'] = $request->validated('assignee');
        }

        try{
            $this->projectRepository->find($project_id);
        }catch (ProjectNotFoundException $e){
            return response()->json(["error" => $e->getMessage()], $e->getCode());
        }

        $new_task['project_id'] = $project_id;
        $task = $this->taskRepository->create($new_task);

        return response()->json(["data" => $task]);
    }

    public function update(PatchTaskFormRequest $request, $project_id, $task_id): JsonResponse
    {
        $data = $request->validated();
        if($request->validated('assignee'))
        {
            $data['assignee_id'] = $request->validated('assignee');
            unset($data['assignee']);
        }

        $task = $this->taskRepository->find($project_id, $task_id);
        $this->taskRepository->update($task->id, $data);

        return response()->json(['data' => $task->fresh()]);
    }

    public function updateTaskStatus($project_id, $task_id, $status): JsonResponse
    {
        $task = $this->taskRepository->find($project_id, $task_id);
        $project = $task->with('project')->first();

       if($project->status === ProjectStatusEnum::Closed->value){
           return response()->json([], 400);
       }

       $this->taskRepository->update($task->id, ['status' => $status]);

       return response()->json([], 204);

    }
}
