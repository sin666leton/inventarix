<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', User::class);
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
            'email' => [
                'required',
                'email:rfc,dns',
                function ($value, $attribute, $fail) {
                    $staff = User::whereHas(
                        'role',
                        function ($query) {
                            $query->where('name', 'staff');
                        }
                    )
                    ->where('id', $this->staff)
                    ->first();

                    if ($staff) {
                        if ($staff->email !== $value) {
                            $exists = User::where('email', $value)->exists();
    
                            if ($exists) $fail("$attribute already exists.");
                        }
                    }
                }
            ]
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'Name',
            'email' => 'Email'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'Field :attribute is required.',
            'email' => ':attribute invalid.',
            'string' => 'Field :attribute must be a string.'
        ];
    }
}
