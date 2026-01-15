<?php

namespace app\dto;

use app\attribute\validation\{Required, Numeric, Min, Max, In, Length};

class CreateOrderDto
{
    #[Required]
    #[Numeric]
    public int $user_id;
    
    #[Required]
    public array $items;
    
    #[Required]
    #[Numeric]
    #[Min(value: 0.01, message: '订单金额必须大于 0')]
    public float $total_amount;
    
    #[Required]
    #[In(values: ['pending', 'paid', 'shipped', 'completed', 'cancelled'])]
    public string $status = 'pending';
    
    #[Length(max: 500)]
    public ?string $note = null;
    
    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->user_id = (int)($data['user_id'] ?? 0);
        $dto->items = $data['items'] ?? [];
        $dto->total_amount = (float)($data['total_amount'] ?? 0);
        $dto->status = $data['status'] ?? 'pending';
        $dto->note = $data['note'] ?? null;
        
        return $dto;
    }
}
