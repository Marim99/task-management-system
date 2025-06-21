<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Http\Resources\TaskResource;
use App\Http\Resources\TaskStoreResource;
use App\Helpers\ApiResponseHelper;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\AssignTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Laravel Task API",
 *      description="Task Management API Documentation",
 *      @OA\Contact(
 *          email="support@example.com"
 *      )
 * )
 *
 * @OA\SecurityScheme(
 *      securityScheme="sanctum",
 *      type="http",
 *      scheme="bearer"
 * )
 *
 * @OA\Tag(
 *     name="Tasks",
 *     description="Task management endpoints"
 * )
 */

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('can:manageTasks')->only(['store', 'updateTask', 'assignTask']);
        $this->middleware('can:viewTask,taskId')->only(['getTaskDetails']);
    }

    /**
     * @OA\Post(
     *     path="/api/tasks",
     *     tags={"Tasks"},
     *     summary="Create a new task",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreTaskRequest")
     *     ),
     *     @OA\Response(response=201, description="Task created successfully"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(StoreTaskRequest $request)
    {
        $request->validate([]);
        $task = Task::create(array_merge($request->all(), [
            'created_by' => auth()->id(),
            'status_id' => 1
        ]));
        return ApiResponseHelper::success(new TaskStoreResource($task), "Task created successfully");
    }
    /**
     * @OA\Put(
     *     path="/api/tasks/{taskId}",
     *     tags={"Tasks"},
     *     summary="Update an existing task",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="taskId",
     *         in="path",
     *         required=true,
     *         description="ID of the task to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateTaskRequest")
     *     ),
     *     @OA\Response(response=200, description="Task updated successfully"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Task not found")
     * )
     */
    public function updateTask(UpdateTaskRequest $request, $taskId)
    {

        $task = Task::findOrFail($taskId);
        $task->update($request->validated());

        return ApiResponseHelper::success(new TaskStoreResource($task), "Task updated successfully");
    }
    /**
     * @OA\Patch(
     *     path="/api/tasks/{taskId}/assign",
     *     tags={"Tasks"},
     *     summary="Assign a task to a user",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="taskId",
     *         in="path",
     *         required=true,
     *         description="ID of the task to assign",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/AssignTaskRequest")
     *     ),
     *     @OA\Response(response=200, description="Task assigned successfully"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Task not found")
     * )
     */
    public function assignTask(AssignTaskRequest $request, $taskId)
    {
        $task = Task::findOrFail($taskId);
        $task->update(['assigned_user_id' => $request->assigned_user_id]);

        return ApiResponseHelper::success(new TaskStoreResource($task), "Task assigned successfully");
    }
    /**
     * @OA\Patch(
     *     path="/api/tasks/{taskId}/status",
     *     tags={"Tasks"},
     *     summary="Update the status of a task",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="taskId",
     *         in="path",
     *         required=true,
     *         description="ID of the task to update status",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateTaskStatusRequest")
     *     ),
     *     @OA\Response(response=200, description="Status updated successfully"),
     *     @OA\Response(response=400, description="Cannot complete task until dependencies are met"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Task not found")
     * )
     */
    public function updateStatus(UpdateTaskStatusRequest $request, $taskId)
    {
        $task = Task::findOrFail(id: $taskId);

        if ($request->status_id == 3 && !$task->isCompletable()) {
            return ApiResponseHelper::error('Cannot complete task until all dependencies are completed', 400);
        }
        $task->update(['status_id' => $request->status_id]);
        return ApiResponseHelper::success(new TaskStoreResource($task), "Status updated successfully");
    }
    /**
     * @OA\Get(
     *     path="/api/tasks",
     *     tags={"Tasks"},
     *     summary="Get a list of tasks with optional filters",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status_id",
     *         in="query",
     *         required=false,
     *         description="Filter tasks by status ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="due_date_from",
     *         in="query",
     *         required=false,
     *         description="Filter tasks with a due date from this date",
     *         @OA\Schema(type="string", format="date", example="2024-03-10")
     *     ),
     *     @OA\Parameter(
     *         name="due_date_to",
     *         in="query",
     *         required=false,
     *         description="Filter tasks with a due date up to this date",
     *         @OA\Schema(type="string", format="date", example="2024-03-20")
     *     ),
     *     @OA\Parameter(
     *         name="assigned_to",
     *         in="query",
     *         required=false,
     *         description="Filter tasks assigned to a specific user ID",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of tasks retrieved successfully",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/TaskResource"))
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function getTasks(Request $request)
    {
        $query = Task::query()
            ->when($request->status_id, fn($q) => $q->where('status_id', $request->status_id))
            ->when(
                $request->due_date_from && $request->due_date_to,
                fn($q) =>
                $q->whereBetween('due_date', [$request->due_date_from, $request->due_date_to])
            )
            ->when($request->assigned_to, fn($q) => $q->where('assigned_user_id', $request->assigned_to));
        if (auth()->user()->role->id === 2) {
            $query->where('assigned_user_id', auth()->id());
        }
        $tasks = $query->get();
        return ApiResponseHelper::success(TaskResource::collection($tasks));
    }
    /**
     * @OA\Get(
     *     path="/api/tasks/{taskId}",
     *     tags={"Tasks"},
     *     summary="Get details of a specific task",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="taskId",
     *         in="path",
     *         required=true,
     *         description="ID of the task to retrieve details",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task details retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/TaskResource")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Task not found"),
     * )
     */
    public function getTaskDetails($taskId)
    {
        $task = Task::with('dependencies', 'assignedUser', 'creator')->findOrFail($taskId);
        return ApiResponseHelper::success(new TaskResource($task->load('dependencies', 'assignedUser', 'creator')));
    }
}
