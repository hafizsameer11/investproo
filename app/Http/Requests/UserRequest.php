<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UserRequest extends FormRequest
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

            'name'             => 'nullable|string|max:255',
            'user_name'        => 'nullable|string|max:255',
            'email'            => 'nullable|email|max:255|unique:users,email',
            'phone'            => 'nullable|string|max:20',
            'role'             => 'nullable|string|in:user,admin', // Adjust values as needed
            'referral_code'    => 'nullable|string|max:255',
            'user_code'        => 'nullable|string|max:255',
            'status'           => 'nullable|string',
            'email_verified_at' => 'nullable|date',
            'password'         => 'nullable|string|min:6',
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
