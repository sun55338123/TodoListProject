<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Services\DailyTaskServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\DateQueryRequest;
use App\Http\Requests\StoreDailyTaskRequest;
use App\Http\Requests\UpdateDailyTaskRequest;
use App\Http\Resources\DailyTaskResource;
use App\Models\DailyTask;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class DailyTaskController extends Controller
{
    public function __construct(
        private readonly DailyTaskServiceInterface $tasks,
        private readonly Gate $gate,
    ) {}

    public function index(DateQueryRequest $request): AnonymousResourceCollection
    {
        $tasks = $this->tasks->listForDate($request->user(), $request->validated('date'));

        return DailyTaskResource::collection($tasks);
    }

    public function store(StoreDailyTaskRequest $request): JsonResponse
    {
        $task = $this->tasks->create($request->user(), $request->validated());

        return (new DailyTaskResource($task))->response()->setStatusCode(201);
    }

    public function update(UpdateDailyTaskRequest $request, DailyTask $dailyTask): DailyTaskResource
    {
        $this->gate->authorize('update', $dailyTask);

        return new DailyTaskResource($this->tasks->update($dailyTask, $request->validated()));
    }

    public function destroy(DailyTask $dailyTask): Response
    {
        $this->gate->authorize('delete', $dailyTask);

        $this->tasks->delete($dailyTask);

        return response()->noContent();
    }
}
