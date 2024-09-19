<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
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
        return  [
            "amount" => "required|numeric|gt:0|max:10000000",
            "aggregator_id" => ["required", Rule::exists('aggregators', 'id')],
            "currency_id" => ["required", Rule::exists('currencies', 'id')],
            "service_name" => ["required", "min:2", "max:100"],
            "service_id" => ["nullable", "max:100"],
            "payer_uuid" => "required|uuid",
            "note" => "required",
            "receiver_uuid" => "nullable|uuid|different:payer_uuid",
        ];
    }
}
