<?php
namespace App\DTOs;

use App\Contracts\DTO;
use Illuminate\Support\Facades\Hash;

class UpdateUserDTO implements DTO
{
    public function __construct(
        public string $name,
        public string $email,
    ) {}

    public static function fromRequest(\Illuminate\Http\Request $request): DTO
    {
        return new self(
            $request->input('name'),
            $request->input('email'),
        );
    }

    public function toArray(): array
    {
        return [
            "name" => $this->name,
            "email" => $this->email
        ];
    }
}