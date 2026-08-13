<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CheckOutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [

            'customer_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'phone_number' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9+\-\s()]+$/', // basic phone format guard
            ],

            'payment_method' => [
                'required',
                'string',
                'in:cash,card,mobile,bkash,nagad,rocket',
            ],

            'vat' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100', // vat is a percentage now
            ],

            'discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'received_amount' => [
                'required',
                'numeric',
                'min:0',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
