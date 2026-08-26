<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'reason',
        'description',
        'proof_video',
        'status',
        'admin_notes',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
