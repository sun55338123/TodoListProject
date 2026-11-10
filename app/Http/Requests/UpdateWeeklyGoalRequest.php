<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWeeklyGoalRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'week_start' => ['sometimes', 'required', 'date_format:Y-m-d'],
            'is_completed' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
