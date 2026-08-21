<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PelacakanBatch extends Model
{
    use HasFactory;

    public const STATUS_DISIAPKAN =
        'Disiapkan';

    public const STATUS_DIPROSES =
        'Diproses';

    public const STATUS_QUERY_SIAP =
        'Query Siap';

    public const STATUS_ENRICHMENT =
        'Enrichment';

    public const STATUS_PERLU_REVIEW =
        'Perlu Review';

    public const STATUS_SELESAI =
        'Selesai';

    public const STATUS_GAGAL =
        'Gagal';


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

        'enrichment_processed_items',
        'identified_items',
        'review_items',
        'not_found_items',
        'enrichment_failed_items',

        'enrichment_started_at',
        'enrichment_finished_at',
    ];


    protected function casts(): array
    {
        return [
            'total_items' =>
                'integer',

            'processed_items' =>
                'integer',

            'success_items' =>
                'integer',

            'failed_items' =>
                'integer',

            'enrichment_processed_items' =>
                'integer',

            'identified_items' =>
                'integer',

            'review_items' =>
                'integer',

            'not_found_items' =>
                'integer',

            'enrichment_failed_items' =>
                'integer',

            'sources' =>
                'array',

            'started_at' =>
                'datetime',

            'finished_at' =>
                'datetime',

            'enrichment_started_at' =>
                'datetime',

            'enrichment_finished_at' =>
                'datetime',
        ];
    }


    public static function statusOptions(): array
    {
        return [
            self::STATUS_DISIAPKAN,
            self::STATUS_DIPROSES,
            self::STATUS_QUERY_SIAP,
            self::STATUS_ENRICHMENT,
            self::STATUS_PERLU_REVIEW,
            self::STATUS_SELESAI,
            self::STATUS_GAGAL,
        ];
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }


    public function items(): HasMany
    {
        return $this->hasMany(
            PelacakanBatchItem::class,
            'pelacakan_batch_id'
        );
    }


    public function kandidats(): HasMany
    {
        return $this->hasMany(
            PelacakanKandidat::class,
            'pelacakan_batch_id'
        );
    }
}