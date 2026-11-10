<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDailyTaskRequest extends FormRequest
{
    /**
     * 週目標是否屬於使用者、是否在同一週，由 DailyTaskService 檢查。
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'task_date' => ['required', 'date_format:Y-m-d'],
            'weekly_goal_id' => ['nullable', 'integer'],
        ];
    }
}
