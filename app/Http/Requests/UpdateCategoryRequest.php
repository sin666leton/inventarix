<?php

namespace App\Http\Requests;

use App\Traits\FailedAuthorizationTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    use FailedAuthorizationTrait;

    public function authorize(): bool
    {
        return $this->user()->can('update', \App\Models\Category::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string'
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'Name'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'Field :attribute is required.',
            'string' => 'Field :attribute should a string.'
        ];
    }
}
