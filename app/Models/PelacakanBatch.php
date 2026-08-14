<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PelacakanBatch extends Model
{
    use HasFactory;

    public const STATUS_DISIAPKAN = 'Disiapkan';

    public const STATUS_DIPROSES = 'Diproses';

    public const STATUS_QUERY_SIAP = 'Query Siap';

    public const STATUS_SELESAI = 'Selesai';

    public const STATUS_GAGAL = 'Gagal';

    protected $fillable = [
        'user_id',
        'nama_batch',
        'status',
        'total_items',
        'processed_items',
        'success_items',
        'failed_items',
        'sources',
        'started_at',
        'finished_at',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'total_items' => 'integer',
            'processed_items' => 'integer',
            'success_items' => 'integer',
            'failed_items' => 'integer',
            'sources' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DISIAPKAN,
            self::STATUS_DIPROSES,
            self::STATUS_QUERY_SIAP,
            self::STATUS_SELESAI,
            self::STATUS_GAGAL,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            PelacakanBatchItem::class,
            'pelacakan_batch_id'
        );
    }
}