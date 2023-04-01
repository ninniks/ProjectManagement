<?php

namespace App\Interfaces;


interface TaskRepositoryInterface
{
    public function all($project_id);
    public function find(string $project_id, $task_id);
    public function create(array $data);
    public function update(string $task_id, array $data);
}
