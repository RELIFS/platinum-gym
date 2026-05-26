<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = ['payment_id', 'invoice_number', 'issued_at', 'due_date', 'subtotal', 'discount', 'tax', 'total', 'status', 'pdf_path', 'meta'];

    protected function casts(): array
    {
        return ['issued_at' => 'date', 'due_date' => 'date', 'subtotal' => 'decimal:2', 'discount' => 'decimal:2', 'tax' => 'decimal:2', 'total' => 'decimal:2', 'meta' => 'array'];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
