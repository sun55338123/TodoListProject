<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDailyTaskRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'task_date' => ['sometimes', 'required', 'date_format:Y-m-d'],
            'weekly_goal_id' => ['sometimes', 'nullable', 'integer'],
            'is_completed' => ['sometimes', 'required', 'boolean'],
            // 打勾時使用者正在檢視的日期。週目標子任務會延到之後的日期，需要記錄實際完成的那一天。
            'completed_on' => ['sometimes', 'required', 'date_format:Y-m-d'],
        ];
    }
}
