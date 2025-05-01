<?php

namespace App\Http\Requests;

use App\Traits\FailedAuthorizationTrait;
use Illuminate\Foundation\Http\FormRequest;

class UpdateItemRequest extends FormRequest
{
    use FailedAuthorizationTrait;

    public function authorize(): bool
    {
        return $this->user()->can('update', \App\Models\Item::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required',
            'stock' => 'required|int'
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'Name',
            'stock' => 'Stock'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'Field :attribute is required.',
            'int' => 'Field :attribute should be a integer.',
        ];
    }
}
