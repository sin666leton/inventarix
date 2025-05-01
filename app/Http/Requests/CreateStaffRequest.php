<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Traits\FailedAuthorizationTrait;
use Illuminate\Foundation\Http\FormRequest;

class CreateStaffRequest extends FormRequest
{
    use FailedAuthorizationTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\User::class);
    }

    public function prepareForValidation()
    {
        $this->merge([
            'role_id' => Role::where('name', 'staff')->first()->value('id')
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'email' => 'required|email:rfc,dns|unique:users,email',
            'password' => 'required|min:8'
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'Name',
            'email' => 'Email address',
            'password' => 'Password'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'Field :attribute is required.',
            'email' => ':attribute invalid.',
            'unique' => ':attribute already exists.',
            'string' => 'Field :attribute must be a string.',
            'min' => 'Field :attribute minimum of :min characters'
        ];
    }
}
