<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetDependenciesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dependencies' => 'required|array',
            'dependencies.*' => 'integer|distinct|min:1',
        ];
    }
} 