<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'file_name', 'source', 'total_rows', 'success_rows', 'failed_rows', 'skipped_rows', 'status', 'error_message', 'meta', 'started_at', 'finished_at'];

    protected function casts(): array
    {
        return ['meta' => 'array', 'started_at' => 'datetime', 'finished_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
