<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportJob extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'type', 'entity', 'filters', 'file_path', 'status', 'error_message', 'finished_at'];

    protected function casts(): array
    {
        return ['filters' => 'array', 'finished_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
