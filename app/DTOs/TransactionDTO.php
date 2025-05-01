<?php
namespace App\DTOs;

use App\Contracts\DTO;
use Illuminate\Http\Request;
use InvalidArgumentException;
use function Illuminate\Events\queueable;

class TransactionDTO implements DTO
{
    public function __construct(
        public int $item_id,
        public int $user_id,
        public string $type,
        public int $quantity,
        public string|null $description = null
    ) {
        if (!in_array($type, ['in', 'out'])) {
            throw new InvalidArgumentException("Invalid transaction type.", 422);
        }
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            $request->input('item_id'),
            $request->user()->id,
            $request->input('type'),
            $request->input('quantity'),
            $request->input('description')
        );
    }

    public function toArray(): array
    {
        return [
            'item_id' => $this->item_id,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'quantity' => $this->quantity,
            'description' => is_null($this->description) ? null : $this->description
        ];
    }
}