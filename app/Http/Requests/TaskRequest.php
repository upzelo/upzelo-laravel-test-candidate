<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // For PUT/PATCH requests, only allow status updates
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            return [
                'status' => 'required|in:pending,in_progress,completed',
            ];
        }

        // For POST requests, require all fields
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'priority' => 'required|in:low,medium,high',
            'project_id' => 'required|exists:projects,id',
            'assigned_to' => 'required|exists:users,id',
            'due_date' => 'nullable|date|after_or_equal:today',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The task title is required.',
            'status.in' => 'Status must be one of: pending, in_progress, completed.',
            'status.required' => 'The status field is required.',
            'priority.in' => 'Priority must be one of: low, medium, high.',
            'project_id.exists' => 'The selected project does not exist.',
            'assigned_to.exists' => 'The selected user does not exist.',
            'due_date.after_or_equal' => 'The due date must be today or a future date.',
        ];
    }

    /**
     * Handle a failed validation attempt for API requests.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
