<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PelacakanBatchItem extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | STATUS PENYIAPAN QUERY
    |--------------------------------------------------------------------------
    */

    public const STATUS_MENUNGGU =
        'Menunggu';

    public const STATUS_DIPROSES =
        'Diproses';

    public const STATUS_QUERY_SIAP =
        'Query Siap';

    public const STATUS_SELESAI =
        'Selesai';

    public const STATUS_GAGAL =
        'Gagal';


    /*
    |--------------------------------------------------------------------------
    | STATUS AUTO ENRICHMENT
    |--------------------------------------------------------------------------
    */

    public const ENRICHMENT_MENUNGGU =
        'Menunggu';

    public const ENRICHMENT_DIPROSES =
        'Diproses';

    public const ENRICHMENT_TERIDENTIFIKASI =
        'Teridentifikasi';

    public const ENRICHMENT_PERLU_VERIFIKASI =
        'Perlu Verifikasi';

    public const ENRICHMENT_TIDAK_DITEMUKAN =
        'Tidak Ditemukan';

    public const ENRICHMENT_GAGAL =
        'Gagal';


    protected $fillable = [
        'pelacakan_batch_id',
        'alumni_id',

        'status',
        'attempts',
        'last_error',
        'processed_at',

        'enrichment_status',
        'enrichment_attempts',
        'enrichment_error',

        'enrichment_started_at',
        'enrichment_finished_at',
    ];


    protected function casts(): array
    {
        return [
            'attempts' =>
                'integer',

            'enrichment_attempts' =>
                'integer',

            'processed_at' =>
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
            self::STATUS_MENUNGGU,
            self::STATUS_DIPROSES,
            self::STATUS_QUERY_SIAP,
            self::STATUS_SELESAI,
            self::STATUS_GAGAL,
        ];
    }


    public static function enrichmentStatusOptions(): array
    {
        return [
            self::ENRICHMENT_MENUNGGU,
            self::ENRICHMENT_DIPROSES,
            self::ENRICHMENT_TERIDENTIFIKASI,
            self::ENRICHMENT_PERLU_VERIFIKASI,
            self::ENRICHMENT_TIDAK_DITEMUKAN,
            self::ENRICHMENT_GAGAL,
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
        return $this->belongsTo(
            Alumni::class
        );
    }


    public function kandidats(): HasMany
    {
        return $this->hasMany(
            PelacakanKandidat::class,
            'pelacakan_batch_item_id'
        );
    }
}