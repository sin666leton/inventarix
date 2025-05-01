<?php
namespace App\DTOs;

use App\Contracts\DTO;


class UpdateItemDTO implements DTO
{
    public function __construct(
        public string $name,
        public int $stock,
    ) {}

    public static function fromRequest(\Illuminate\Http\Request $request): DTO
    {
        return new self(
            name: $request->input('name'),
            stock: $request->input('stock')
        );
    }

    public function toArray(): array
    {
        return [
            "name" => $this->name,
            "stock" => $this->stock
        ];
    }
}