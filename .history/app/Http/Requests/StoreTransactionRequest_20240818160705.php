<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTransactionRequest extends FormRequest
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
            'amount' => 'required|numeric',
            'timestamp' => 'required|date_format:Y-m-d\TH:i:s.v\Z',
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'amount.required' => 'O campo de valor é obrigatório.',
            'amount.numeric' => 'O valor deve ser numérico.',
            'timestamp.required' => 'O campo de timestamp é obrigatório.',
            'timestamp.date_format' => 'O timestamp deve estar no formato Y-m-d\TH:i:s.v\Z.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        $content = $this->getContent();
        json_decode($content);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $response = response()->json(400);

            throw new HttpResponseException($response);
        }

        $errors = $validator->errors();
        $response = response()->json([
            'success' => false,
            'message' => 'Validação falhou.',
            'errors' => $errors->messages(),
        ], 422);

        throw new HttpResponseException($response);
    }
}
