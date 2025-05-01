<?php
namespace App\DTOs;

use App\Contracts\DTO;


class CreateItemDTO implements DTO
{
    public function __construct(
        public string $name,
        public string $code,
        public int $stock,
    ) {}

    public static function fromRequest(\Illuminate\Http\Request $request): DTO
    {
        return new self(
            name: $request->input('name'),
            code: $request->input('code'),
            stock: $request->input('stock')
        );
    }

    public function toArray(): array
    {
        return [
            "name" => $this->name,
            "code" => $this->code,
            "stock" => $this->stock
        ];
    }
}