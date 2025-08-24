<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class InvestmentPlanRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'plan_name'         => 'nullable|string|max:255',
            'description'         => 'nullable|string|max:1000',
            'min_amount'        => 'nullable|numeric|min:0',
            'max_amount'        => 'nullable|numeric|min:0',
            'profit_percentage' => 'nullable|numeric|min:0',
            'duration'          => 'nullable|integer|min:1',
            'status'            => 'nullable|string|max:255',
        ];
    }
      protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'data' => $validator->errors(),
                'message' => $validator->errors()->first()
            ], 422)
        );
    }
}
