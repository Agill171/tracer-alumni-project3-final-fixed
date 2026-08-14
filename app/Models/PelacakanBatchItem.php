<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PelacakanBatchItem extends Model
{
    use HasFactory;

    public const STATUS_MENUNGGU = 'Menunggu';

    public const STATUS_DIPROSES = 'Diproses';

    public const STATUS_QUERY_SIAP = 'Query Siap';

    public const STATUS_SELESAI = 'Selesai';

    public const STATUS_GAGAL = 'Gagal';

    protected $fillable = [
        'pelacakan_batch_id',
        'alumni_id',
        'status',
        'attempts',
        'last_error',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'processed_at' => 'datetime',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_MENUNGGU,
            self::STATUS_DIPROSES,
            self::STATUS_QUERY_SIAP,
            self::STATUS_SELESAI,
            self::STATUS_GAGAL,
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(
            PelacakanBatch::class,
            'pelacakan_batch_id'
        );
    }

    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }
}
