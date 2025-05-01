<?php

namespace App\Http\Requests;

use App\Traits\FailedAuthorizationTrait;
use Illuminate\Foundation\Http\FormRequest;

class ChangeNameRequest extends FormRequest
{
    use FailedAuthorizationTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|min:3'
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
            'string' => 'Field :attribute must be a string.',
            'min' => 'Field :attribute minimum of :min characters'
        ];
    }
}
