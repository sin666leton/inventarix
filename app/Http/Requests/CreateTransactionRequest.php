<?php

namespace App\Http\Requests;

use App\Enums\TransactionType;
use App\Traits\FailedAuthorizationTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTransactionRequest extends FormRequest
{
    use FailedAuthorizationTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Transaction::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'item_id' => 'required|exists:items,id',
            'user_id' => 'required|exists:users,id',
            'quantity' => 'required|int|min:1',
            'type' => ['required', Rule::enum(TransactionType::class)],
            'description' => 'nullable'
        ];
    }

    public function attributes()
    {
        return [
            'item_id' => 'Item',
            'user_id' => 'User',
            'quantity' => 'Quantity',
            'type' => 'Type',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'Field :attribute is required.',
            'int' => 'Field :attribute should be a integer.',
            'exists' => 'Field :attribute not exists.',
            'min' => 'Field :attribute is not less than :min.',
            'enum' => 'Field :attribute must be one of the allowed types.'
        ];
    }
}
