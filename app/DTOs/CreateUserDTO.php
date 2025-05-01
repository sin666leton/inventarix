<?php
namespace App\DTOs;

use App\Contracts\DTO;
use Illuminate\Support\Facades\Hash;


class CreateUserDTO implements DTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $role_id
    ) {}

    public static function fromRequest(\Illuminate\Http\Request $request): DTO
    {
        return new self(
            $request->input('name'),
            $request->input('email'),
            $request->input('password'),
            $request->input('role_id')
        );
    }

    public function toArray(): array
    {
        return [
            "name" => $this->name,
            "email" => $this->email,
            'password' => Hash::make($this->password),
            'role_id' => $this->role_id
        ];
    }
}