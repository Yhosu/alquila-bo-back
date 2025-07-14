<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ConfirmSubscriptionRequest extends FormRequest
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
    public function rules(): array {
        return [
            'tokenConfirmSubscription'     => 'required',
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator) {
        $errors = $validator->errors()->all();
        $response = [
            'status'  => false,
            'message' => 'Campos Obligatorios',
            'errors'  => $errors
        ];
        throw new ValidationException($validator, response()->json($response, 400));
    }
}
