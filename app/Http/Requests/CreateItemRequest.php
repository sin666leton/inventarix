<?php

namespace App\Http\Requests;

use App\Traits\FailedAuthorizationTrait;
use Illuminate\Foundation\Http\FormRequest;

class CreateItemRequest extends FormRequest
{
    use FailedAuthorizationTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Item::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required',
            'code' => 'required|unique:items,code',
            'stock' => 'required|int'
        ];
    }

    public function attributes()
    {
        return [
            'category_id' => 'Category',
            'name' => 'Name',
            'code' => 'Code',
            'stock' => 'Stock'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'Field :attribute is required.',
            'int' => 'Field :attribute should be a integer.',
            'unique' => 'Field :attribute should be unique.',
            'exists' => 'Field :attribute not exists.'
        ];
    }
}
