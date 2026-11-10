<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWeeklyGoalRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            // 可以是該週任一天，儲存時轉換為該週星期一。
            'week_start' => ['required', 'date_format:Y-m-d'],
        ];
    }
}
