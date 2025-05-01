<?php

namespace App\Http\Requests;

use App\Traits\FailedAuthorizationTrait;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
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
            'new_password' => 'required|min:8',
            'old_password' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'new_password' => 'New password',
            'old_password' => 'Old password'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'Field :attribute is required.',
            'min' => 'Field :attribute minimum of :min characters'
        ];
    }
}
