<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PelacakanQuery extends Model
{
    use HasFactory;

    protected $fillable = [
        'alumni_id',
        'user_id',
        'sumber',
        'prioritas',
        'query',
        'query_hash',
        'url_pencarian',
        'status',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'prioritas' => 'integer',
            'generated_at' => 'datetime',
        ];
    }

    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
