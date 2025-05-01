<?php
namespace App\DTOs;

use App\Contracts\DTO;


class CategoryDTO implements DTO
{
    public function __construct(
        public string $name,
        public ?string $description
    ) {}

    public static function fromRequest(\Illuminate\Http\Request $request): DTO
    {
        return new self(
            name: $request->input('name'),
            description: $request->input('description')
        );
    }

    public function toArray(): array
    {
        return [
            "name" => $this->name,
            "description" => $this->description
        ];
    }
}