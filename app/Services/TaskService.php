<?php
namespace App\Services;

use App\Services\Contracts\TaskServiceInterface;
use App\Repositories\TaskRepository;
use App\Models\Task;
class TaskService extends BaseService implements TaskServiceInterface
{
    public function __construct(TaskRepository $repository)
    {
        parent::__construct($repository);
    }

    public function filteredList(array $filters)
    {
        return $this->repository->filteredList($filters);
    }
    public function setDependencies(Task $task, array $dependencyIds): string
    {
        if (in_array($task->id, $dependencyIds)) {
            throw new \InvalidArgumentException('A task cannot depend on itself.');
        }

        $existingCount = Task::whereIn('id', $dependencyIds)->count();
        if ($existingCount !== count($dependencyIds)) {
            throw new \InvalidArgumentException('One or more dependency task IDs are invalid.');
        }

        $task->dependencies()->sync($dependencyIds);

        return 'Dependencies set successfully.';
    }
}