<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Contracts\TaskServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Http\Requests\AssignTaskRequest;
use App\Http\Requests\FilteredListTaskRequest;
use App\Http\Requests\SetDependenciesRequest;
class TaskController extends Controller
{
    public function __construct(protected TaskServiceInterface $taskService)
    {
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        $data['status_id'] = TaskStatus::PENDING;

        $task = $this->taskService->create($data);

        return response()->json($task, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $task = $this->taskService->find($id);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $task = $this->taskService->update($id, $request->validated());

        return response()->json(['data' => $task, 'message' => 'Task updated successfully.']);
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->taskService->find($id);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $this->taskService->delete($id);

        return response()->json(['message' => 'Task soft-deleted.']);
    }

    public function updateStatus(UpdateTaskStatusRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();

        try {
            $task = $this->taskService->find($id);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        if ($task->assigned_to !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ((int) $validated['status_id'] === TaskStatus::COMPLETED->value) {
            $incompleteDeps = $task->dependencies()->where('status_id', '!=', TaskStatus::COMPLETED->value)->count();
            if ($incompleteDeps > 0) {
                return response()->json(['message' => 'Cannot complete task with incomplete dependencies.'], 422);
            }
        }

        $task->status_id = $validated['status_id'];
        $task->save();

        return response()->json(['data' => $task, 'message' => 'Status updated.']);
    }

    public function assign(AssignTaskRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();

        try {
            $task = $this->taskService->find($id);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $task->assigned_to = $validated['user_id'];
        $task->save();

        return response()->json([
            'message' => 'Task successfully assigned to the user.',
            'data' => $task->load('assignee'),
        ]);
    }

    public function filteredList(FilteredListTaskRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $page = $validated['page'] ?? 1;
        $pageSize = $validated['page_size'] ?? 10;

        $tasksQuery = $this->taskService->filteredList($validated); // true = return query builder
        $tasks = $tasksQuery->paginate($pageSize, ['*'], 'page', $page);

        return response()->json([
            'data' => $tasks->items(),
            'current_page' => $tasks->currentPage(),
            'per_page' => $tasks->perPage(),
            'total' => $tasks->total(),
            'total_pages' => $tasks->lastPage(),
        ]);
    }

    public function myTasks(Request $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $pageSize = $request->input('page_size', 10);
        $user = $request->user();
        $tasks = $user->tasks()->with(['status', 'createdBy'])->latest()->paginate($pageSize, ['*'], 'page', $page);

        return response()->json([
            'data' => $tasks->items(),
            'current_page' => $tasks->currentPage(),
            'per_page' => $tasks->perPage(),
            'total' => $tasks->total(),
            'total_pages' => $tasks->lastPage(),
        ]);
    }

    public function setDependencies(SetDependenciesRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();

        $task = Task::findOrFail($id);

        $dependencyIds = $validated['dependencies'];

        if (in_array($id, $dependencyIds)) {
            return response()->json(['message' => 'A task cannot depend on itself.'], 422);
        }

        $foundTasksCount = Task::whereIn('id', $dependencyIds)->count();
        if ($foundTasksCount !== count($dependencyIds)) {
            return response()->json(['message' => 'One or more dependency task IDs are invalid.'], 422);
        }

        $task->dependencies()->sync($dependencyIds);

        return response()->json(['message' => 'Dependencies set successfully.']);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $task = $this->taskService->find($id);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $user = $request->user();

        if ($user->hasRole('user') && $task->assigned_to !== $user->id) {
            return response()->json(['message' => 'Forbidden: Task not assigned to you.'], 403);
        }

        $task->load(['dependencies:id,title,status_id', 'status', 'assignee']);

        return response()->json(['data' => $task]);
    }
}
