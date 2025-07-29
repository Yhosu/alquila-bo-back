<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ReservationFormRequest extends FormRequest
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
            'product_id'  => 'required',
            'init_date'   => 'required|date',
            'finish_date' => 'required|date'
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
