<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Services\WeeklyGoalServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\DateQueryRequest;
use App\Http\Requests\StoreWeeklyGoalRequest;
use App\Http\Requests\UpdateWeeklyGoalRequest;
use App\Http\Resources\WeeklyGoalCollection;
use App\Http\Resources\WeeklyGoalResource;
use App\Models\WeeklyGoal;
use App\Support\Week;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class WeeklyGoalController extends Controller
{
    public function __construct(
        private readonly WeeklyGoalServiceInterface $goals,
        private readonly Gate $gate,
    ) {}

    /**
     * 回傳 date 所在週（星期一至星期日）的目標與完成進度。
     */
    public function index(DateQueryRequest $request): WeeklyGoalCollection
    {
        $week = Week::containing($request->validated('date'));

        return new WeeklyGoalCollection($this->goals->listForWeek($request->user(), $week), $week);
    }

    public function store(StoreWeeklyGoalRequest $request): JsonResponse
    {
        $goal = $this->goals->create($request->user(), $request->validated());

        return (new WeeklyGoalResource($goal))->response()->setStatusCode(201);
    }

    public function update(UpdateWeeklyGoalRequest $request, WeeklyGoal $weeklyGoal): WeeklyGoalResource
    {
        $this->gate->authorize('update', $weeklyGoal);

        return new WeeklyGoalResource($this->goals->update($weeklyGoal, $request->validated()));
    }

    public function destroy(WeeklyGoal $weeklyGoal): Response
    {
        $this->gate->authorize('delete', $weeklyGoal);

        $this->goals->delete($weeklyGoal);

        return response()->noContent();
    }
}
