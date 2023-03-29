<?php

namespace App\Interfaces;


interface TaskRepositoryInterface
{
    public function all($project_id);
    public function find(string $project_id, $task_id);
}
