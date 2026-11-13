<?php

namespace App\Http\Requests;

use App\Services\Flood\FloodRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PredictFloodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rainfall' => ['required', 'numeric', 'between:0,15000'],
            'elevation' => ['required', 'numeric', 'between:-500,9000'],
            'slope' => ['required', 'numeric', 'between:0,90'],
            'distance' => ['required', 'numeric', 'between:0,1000000'],
            'landuse' => ['required', Rule::in(app(FloodRepository::class)->data()['meta']['landuses'])],
        ];
    }

    public function attributes(): array
    {
        return ['rainfall' => 'curah hujan', 'elevation' => 'elevasi', 'slope' => 'kemiringan lereng', 'distance' => 'jarak sungai', 'landuse' => 'penggunaan lahan'];
    }

    public function messages(): array
    {
        return ['required' => ':attribute wajib diisi.', 'numeric' => ':attribute harus berupa angka.', 'between' => ':attribute harus antara :min dan :max.', 'in' => 'Pilihan :attribute tidak valid.'];
    }
}
