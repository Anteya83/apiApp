<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class OutStoreRequest extends FormRequest
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
            'date' => 'date_format:Y-m-d H:i:s|before_or_equal:now',
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer|gt:0',
            'items.*.quantity' => 'required|integer|gt:0',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        // Создаем исключение валидации
        throw new ValidationException($validator, response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422));
    }
}
