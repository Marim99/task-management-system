<?php
namespace App\Services\Contracts;
use App\Models\Task;
interface TaskServiceInterface extends BaseServiceInterface
{
   public function filteredList(array $filters);
   public function setDependencies(Task $task, array $dependencyIds);

}