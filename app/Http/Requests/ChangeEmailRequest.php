<?php

namespace App\Http\Requests;

use App\Traits\FailedAuthorizationTrait;
use Illuminate\Foundation\Http\FormRequest;

class ChangeEmailRequest extends FormRequest
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
            'new_email' => 'required|email:rfc,dns|unique:users,email',
            'password' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'new_email' => 'New email',
            'password' => 'Password'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'Field :attribute is required.',
            'email' => 'Invalid :attribute',
            'unique' => ':attribute must be a unique'
        ];
    }
}
