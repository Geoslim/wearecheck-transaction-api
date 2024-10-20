<?php

namespace App\Http\Requests;

use App\Enums\TransactionType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTransactionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(TransactionType::cases())],
            'amount' => ['required', 'numeric'],
            'reference' => [
                Rule::requiredIf(function () {return $this->{'type'} == TransactionType::CREDIT->value;}),
                'string'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'reference.required' => 'The reference is required for a credit transaction type.'
        ];
    }
}
