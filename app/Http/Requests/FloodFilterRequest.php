<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FloodFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'method' => ['nullable', Rule::in(['kmeans', 'fcm'])],
            'risk' => ['nullable', Rule::in(['Tinggi', 'Sedang', 'Rendah'])],
            'landuse' => ['nullable', 'string', 'max:100'],
            'transition' => ['nullable', 'boolean'],
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
